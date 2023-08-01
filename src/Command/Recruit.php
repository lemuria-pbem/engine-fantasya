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
use Lemuria\Engine\Fantasya\Message\Unit\RecruitReducedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RecruitTooExpensiveMessage;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Orc;
use Lemuria\Model\Fantasya\Relation;
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

	private const KNOWLEDGE = [Orc::class => [Bladefighting::class => 1, Spearfighting::class => 1]];

	private int $demand;

	private int $size;

	public function canBeCentralized(): bool {
		return true;
	}

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

	protected function checkSize(): bool {
		return true;
	}

	/**
	 * Do the check before allocation.
	 */
	protected function getCheckBeforeAllocation(): array {
		$creator = $this->context->UnitMapper()->getCreator($this->unit);
		if ($creator) {
			return $this->getCheckByAgreementForUnit($creator, Relation::RESOURCES);
		}
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

		$peasant      = self::createCommodity(Peasant::class);
		$this->demand = $size;
		$free         = $this->getFreeSpace();
		if ($free < $size) {
			$size = $free;
		}

		if (!$this->isRunCentrally) {
			$region = $this->unit->Region();
			$quota  = $this->unit->Party()->Regulation()->getQuotas($region)?->getQuota($peasant)?->Threshold();
			if (is_int($quota) && $quota > 0) {
				$peasants  = $region->Resources()[$peasant]->Count();
				$available = max(0, $peasants - $quota);
				if ($available < $size) {
					$size = $available;
					Lemuria::Log()->debug('Peasant availability reduced due to quota.');
				}
			}
		}

		$this->size = $size;
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
		$race    = $this->unit->Race()::class;
		$minimum = self::KNOWLEDGE[$race] ?? [];

		if ($oldSize > 0) {
			$percent = $oldSize / $newSize;
			foreach ($this->unit->Knowledge() as $ability) {
				$talent = $ability->Talent();
				if (isset($minimum[$talent::class])) {
					$oldExperience = $ability->Experience();
					$newExperience = Ability::getExperience($minimum[$talent::class]);
					$added         = $newSize - $oldSize;
					$experience    = (int)floor(($oldSize * $oldExperience + $added * $newExperience) / $newSize);
					$ability->removeItem(new Ability($talent, $oldExperience - $experience));
				} else {
					$experience    = $ability->Experience();
					$newExperience = (int)round($percent * $experience);
					$ability->removeItem(new Ability($ability->Talent(), $experience - $newExperience));
				}
			}
			$this->message(RecruitKnowledgeMessage::class)->p((int)round(100.0 * $percent));
		} else {
			$knowledge = $this->unit->Knowledge();
			$knowledge->clear();
			foreach ($minimum as $talent => $level) {
				$knowledge->add(new Ability(self::createTalent($talent), Ability::getExperience($level)));
			}
		}
	}
}
