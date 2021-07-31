<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Party\RecruitPreventMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitKnowledgeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitLessMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitPaymentMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitTooExpensiveMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Orc;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Bladefighting;
use Lemuria\Model\Fantasya\Talent\Spearfighting;

/**
 * Implementation of command REKRUTIEREN (recruit peasants).
 *
 * The command pays peasants and increases a unit's size.
 *
 * - REKRUTIEREN <number>
 */
final class Recruit extends AllocationCommand
{
	use BuilderTrait;
	use CollectTrait;

	private int $size;

	protected function run(): void {
		parent::run();
		$guardParties = $this->checkBeforeAllocation();
		if (empty($guardParties)) {
			$quantity = $this->getResource(Peasant::class);
			$size     = $quantity->Count();
			$payable  = $this->getMaximumPayable($size);
			if ($payable > 0) {
				$oldSize = $this->unit->Size();
				$newSize = $oldSize + $payable;
				$this->reduceKnowledge($oldSize, $newSize);
				$this->unit->setSize($newSize);
			}
			if ($size === $this->size) {
				$this->message(RecruitMessage::class)->p($size);
			} else {
				if ($payable < $size) {
					$this->message(RecruitTooExpensiveMessage::class)->p($size);
				} else {
					$this->message(RecruitLessMessage::class)->p($size);
				}
			}
		} else {
			$this->message(RecruitGuardedMessage::class);
			foreach ($guardParties as $party) {
				$this->message(RecruitPreventMessage::class, $party)->e($this->unit);
			}
		}
	}

	/**
	 * Do the check before allocation.
	 */
	protected function getCheckBeforeAllocation(): array {
		return $this->getCheckByAgreement(Relation::RESOURCES);
	}

	/**
	 * Determine the demand.
	 */
	protected function createDemand(): void {
		$size = (int)$this->phrase->getParameter();
		if (!$size) {
			throw new InvalidCommandException('Invalid size "' . $size . '".');
		}

		$construction = $this->unit->Construction();
		if ($construction) {
			$space = $construction->Size();
			$used  = $construction->Inhabitants()->count();
			$free  = max(0, $space - $used);
			if ($free < $size) {
				$size = $free;
				Lemuria::Log()->debug('Number of recruits reduced to ' . $free . '.');
			}
		}

		$this->size = $size;
		$peasant    = self::createCommodity(Peasant::class);
		$this->resources->add(new Quantity($peasant, $size));
	}

	/**
	 * Determine the number of recruits that can be paid.
	 */
	private function getMaximumPayable(int $payable): int {
		$silver       = self::createCommodity(Silver::class);
		$price        = $this->unit->Party()->Race()->Recruiting();
		$inventory    = $this->unit->Inventory();
		$neededSilver = $payable * $price;
		$this->collectQuantity($this->unit, $silver, $neededSilver);
		$ownSilver = $inventory->offsetGet($silver)->Count();
		if ($ownSilver < $neededSilver) {
			$payable      = (int)floor($ownSilver / $price);
			$neededSilver = $payable * $price;
		}
		$payment = new Quantity($silver, $neededSilver);
		$this->unit->Inventory()->remove($payment);
		$this->message(RecruitPaymentMessage::class)->i($payment)->p($payable);
		return $payable;
	}

	private function reduceKnowledge(int $oldSize, int $newSize): void {
		if ($oldSize > 0) {
			$percent = $oldSize / $newSize;
			foreach ($this->unit->Knowledge() as $ability/* @var Ability $ability */) {
				$experience    = $ability->Experience();
				$newExperience = (int)round($percent * $experience);
				$ability->removeItem(new Ability($ability->Talent(), $experience - $newExperience));
			}
			$this->message(RecruitKnowledgeMessage::class)->p((int)round(100.0 * $percent));
		}

		if ($this->unit->Race() instanceof Orc) {
			$minimum = Ability::getExperience(1);
			$this->setMinimumExperience(self::createTalent(Bladefighting::class), $minimum);
			$this->setMinimumExperience(self::createTalent(Spearfighting::class), $minimum);
		}
	}

	private function setMinimumExperience(Talent $talent, int $minimum): void {
		$knowledge = $this->unit->Knowledge();
		if (isset($knowledge[$talent])) {
			$ability    = $knowledge[$talent];
			$experience = $ability->Experience();
			if ($experience < $minimum) {
				$ability->addItem(new Ability($talent, $experience - $minimum));
			}
		} else {
			$knowledge->add(new Ability($talent, $minimum));
		}
	}
}
