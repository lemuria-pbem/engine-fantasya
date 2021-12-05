<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\SiegeEffect;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Unit;

trait SiegeTrait
{
	private ?SiegeEffect $siege = null;

	protected function initSiege(?Construction $construction = null): self {
		if ($construction) {
			$this->siege = new SiegeEffect(State::getInstance());
			$this->siege->setConstruction($construction);
			$effect = Lemuria::Score()->find($this->siege);
			if ($effect instanceof SiegeEffect) {
				$this->siege = $effect;
			}
		}
		return $this;
	}

	protected function isSieged(?Construction $construction = null): bool {
		if ($construction) {
			$this->initSiege($construction);
		}
		return $this->siege && $this->siege->IsActive();
	}

	protected function canEnterOrLeave(Unit $unit): bool {
		if (!$this->siege) {
			$this->initSiege($unit->Construction());
		}
		if ($this->isSieged()) {
			$calculus = new Calculus($unit);
			return $calculus->knowledge(Camouflage::class)->Level() > $this->siege->Perception();
		}
		return true;
	}

	protected function isStoppedBySiege(Unit $we, Unit $other): bool {
		$ownConstruction = $we->Construction();
		$otherConstruction = $other->Construction();
		if ($ownConstruction === $otherConstruction) {
			return false;
		}

		if ($ownConstruction && !$otherConstruction) {
			$this->initSiege($ownConstruction);
			return $this->canEnterOrLeave($we) || $this->canEnterOrLeave($other);
		}

		if (!$ownConstruction && $otherConstruction) {
			$this->initSiege($otherConstruction);
			return $this->canEnterOrLeave($we) || $this->canEnterOrLeave($other);
		}

		$this->initSiege($ownConstruction);
		$weCanLeave    = $this->canEnterOrLeave($we);
		$otherCanEnter = $this->canEnterOrLeave($other);
		$this->initSiege($otherConstruction);
		$weCanEnter    = $this->canEnterOrLeave($we);
		$otherCanLeave = $this->canEnterOrLeave($other);
		return $weCanLeave && $weCanEnter || $otherCanLeave && $otherCanEnter || $weCanLeave && $otherCanLeave;
	}
}
