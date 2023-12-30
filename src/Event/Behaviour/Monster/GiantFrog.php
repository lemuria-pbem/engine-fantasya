<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Effect\TravelEffect;
use function Lemuria\randFloatBetween;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\AttackOnUnits;
use Lemuria\Engine\Fantasya\Event\Act\Attack;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Event\Act\Seek;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Calendar\Season;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;

class GiantFrog extends AbstractBehaviour
{
	private const array REPRODUCTION = [1.25, 2.0];

	private const int MAX_UNITS = 10;

	private const int MAX_FROGS = 10;

	/**
	 * Giant frogs reproduce in late Spring.
	 */
	public function Reproduction(): Reproduction {
		$reproduction = parent::Reproduction();
		$calendar     = Lemuria::Calendar();
		if ($calendar->Season() === Season::Spring && $calendar->Week() === 3) {
			$calculus  = new Calculus($this->unit);
			$kinsmen   = $calculus->getKinsmen()->add($this->unit);
			if ($kinsmen->count() < self::MAX_UNITS || $kinsmen->Size() < self::MAX_UNITS * self::MAX_FROGS) {
				$this->unit->setSize((int)round(randFloatBetween(...self::REPRODUCTION) * $this->unit->Size()));
				if ($reproduction->Chance() > 0.0) {
					Lemuria::Log()->debug($this->unit . ' will reproduce this round.');
				}
			}
		}
		return $reproduction;
	}

	public function prepare(): static {
		if (Lemuria::Calendar()->Season() === Season::Winter) {
			return $this->hibernate();
		}

		$effect = $this->getAttackEffect();
		if ($effect) {
			$enemy = $effect->Units();
			if ($enemy->count()) {
				$attack = new Attack($this);
				$attack->setEnemy($enemy)->act();
			}
		}

		return $this;
	}

	public function conduct(): static {
		if ($this->hibernate) {
			return $this;
		}
		return $this->reproduce()->lurk();
	}

	public function finish(): static {
		parent::finish();

		$this->seekUnperceptive();
		if ($this->act instanceof Seek) {
			$enemy     = $this->act->Enemy();
			$travelled = [];
			$effect    = new TravelEffect(State::getInstance());
			foreach ($enemy as $unit) {
				if (Lemuria::Score()->find($effect->setUnit($unit))) {
					$travelled[] = $unit;
				}
			}
			if (!empty($travelled)) {
				$attacked = $this->getAttackedUnits($this->unit);
				foreach ($travelled as $unit) {
					$attacked->add($unit);
				}
			}
		}

		$this->split(self::MAX_FROGS);
		if ($this->act instanceof Create && isset($attacked)) {
			foreach ($this->act->getUnits() as $unit) {
				$this->getAttackedUnits($unit)->fill($attacked);
			}
		}

		return $this;
	}

	protected function getAttackEffect(): ?AttackOnUnits {
		$effect   = new AttackOnUnits(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($this->unit));
		return $existing instanceof AttackOnUnits ? $existing : null;
	}

	protected function getAttackedUnits(Unit $unit): People {
		$effect   = new AttackOnUnits(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($unit));
		if ($existing instanceof AttackOnUnits) {
			return $existing->Units();
		}
		Lemuria::Score()->add($effect);
		return $effect->Units();
	}
}
