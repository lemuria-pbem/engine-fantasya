<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\Daydream;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\DaydreamProductivityMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity\Potion\DrinkOfCreation;
use Lemuria\Model\Fantasya\Talent;

trait WorkloadTrait
{
	use ContextTrait;

	protected int $fullProduction;

	protected Workload $workload;

	protected function initWorkload(int $maximum = 1000000): void {
		$this->workload = $this->context->getWorkload($this->unit)->setMaximum($maximum);
	}

	/**
	 * Calculate reduced production for current workload.
	 */
	protected function reduceByWorkload(int $fullProduction): int {
		$this->fullProduction = $fullProduction;
		$remaining            = max(0.0, 1.0 - $this->workload->Percent());
		return (int)round($remaining * $fullProduction);
	}

	protected function addToWorkload(int $production): void {
		$this->workload->add((int)round($production / $this->fullProduction * $this->workload->Maximum()));
	}

	protected function undoWorkload(int $production): void {
		$this->workload->add(-(int)round($production / $this->fullProduction * $this->workload->Maximum()));
	}

	protected function potionBoost(int $unitSize): float {
		if ($unitSize > 0) {
			$effect     = $this->context->getCalculus($this->unit)->hasApplied(DrinkOfCreation::class);
			$potionSize = $effect?->Count() * DrinkOfCreation::PERSONS;
			return min(2.0, 1.0 + $potionSize / $unitSize);
		}
		return 0.0;
	}

	protected function getProductivity(Ability|Talent|string $talent, ?Calculus $calculus = null): Ability {
		if ($talent instanceof Ability) {
			$talent = $talent->Talent();
		}
		if (!$calculus) {
			$calculus = $this->calculus();
		}
		$unit      = $calculus->Unit();
		$knowledge = $calculus->knowledge($talent);
		$level     = $knowledge->Level();
		$effect    = new Daydream(State::getInstance());
		$effect    = Lemuria::Score()->find($effect->setUnit($unit));
		if ($effect instanceof Daydream) {
			$level = max(1, $level - $effect->Level());
			$this->message(DaydreamProductivityMessage::class, $unit);
			return new Ability($knowledge->Talent(), Ability::getExperience($level));
		}
		return $knowledge;
	}
}
