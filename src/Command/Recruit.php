<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Party\RecruitPreventMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitKnowledgeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitLessMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitPaymentMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitReducedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitTooExpensiveMessage;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
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
	use StatisticsTrait;

	private int $demand;

	private int $size;

	protected function run(): void {
		$tooMuch = $this->size - $this->getFreeSpace();
		if ($tooMuch > 0) {
			$this->size -= $tooMuch;
			$this->resources->remove(new Quantity(self::createCommodity(Peasant::class), $tooMuch));
		}

		if ($this->size < $this->demand) {
			$this->message(RecruitReducedMessage::class)->p($this->size);
		}
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

	#[Pure] protected function checkSize(): bool {
		return true;
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
		if ($size <= 0) {
			throw new InvalidCommandException('Invalid size "' . $size . '".');
		}

		$this->demand = $size;
		$free         = $this->getFreeSpace();
		if ($free < $size) {
			$size = $free;
		}
		$this->size = $size;
		$peasant    = self::createCommodity(Peasant::class);
		$this->resources->add(new Quantity($peasant, $size));
	}

	private function getFreeSpace(): int {
		$construction = $this->unit->Construction();
		if ($construction) {
			return $construction->getFreeSpace();
		}
		return PHP_INT_MAX;
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
		$this->placeDataMetrics(Subject::Recruiting, $neededSilver, $this->unit);
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
			/** @var Ability $ability */
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
