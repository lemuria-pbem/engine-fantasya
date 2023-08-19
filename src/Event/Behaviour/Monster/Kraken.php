<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\AttackOnVessel;
use Lemuria\Engine\Fantasya\Event\Act\Attack;
use Lemuria\Engine\Fantasya\Event\Act\Roam;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Calendar\Season;
use Lemuria\Model\Fantasya\Fleet;
use Lemuria\Model\Fantasya\Ship\Caravel;
use Lemuria\Model\Fantasya\Ship\Dragonship;
use Lemuria\Model\Fantasya\Ship\Galleon;
use Lemuria\Model\Fantasya\Ship\Longboat;
use Lemuria\Model\Fantasya\Ship\Trireme;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Vessel;

class Kraken extends AbstractBehaviour
{
	protected const CHANCE = 0.5;

	protected const REPRODUCTION = 1;

	protected const CURIOSITY_SMALL = 0.2;

	protected const CURIOSITY_BIG = 0.8;

	protected const MAX_PROVOKE = 0.9;

	protected const PERISH = 0.01;

	/**
	 * Two or more krakens in the same region reproduce if there are no other krakens in the neighbour regions.
	 */
	public function Reproduction(): Reproduction {
		$reproduction = parent::Reproduction();
		if (Lemuria::Calendar()->Season() !== Season::Winter) {
			$calculus = new Calculus($this->unit);
			$kinsmen  = $calculus->getKinsmen()->add($this->unit);
			if ($kinsmen->Size() >= 2) {
				if ($calculus->getRelatives()->count() <= 0) {
					$reproduction->setChance(self::CHANCE)->setSize(self::REPRODUCTION);
					Lemuria::Log()->debug($this->unit . ' may reproduce this round.');
				}
			}
		}
		return $reproduction;
	}

	public function prepare(): static {
		$effect = $this->getAttackEffect();
		if ($effect) {
			$enemy = $effect->Vessel()->Passengers();
			if ($enemy->count()) {
				$attack = new Attack($this);
				$attack->setEnemy($enemy)->act();
			}
		}
		return $this;
	}

	public function conduct(): static {
		return $this->perishByChance(self::PERISH)->reproduceAndLeaveOrRoam();
	}

	public function finish(): static {
		parent::finish();
		if ($this->roam instanceof Roam && !$this->roam->HasMoved()) {
			$big   = new Fleet();
			$small = new Fleet();
			foreach ($this->unit->Region()->Fleet() as $vessel) {
				switch ($vessel->Ship()::class) {
					case Caravel::class :
					case Galleon::class :
					case Trireme::class :
						$big->add($vessel);
						break;
					case Dragonship::class :
					case Longboat::class :
						$small->add($vessel);
						break;
				}
			}
			$victim = $this->getVictim($big, $small);
			if ($victim) {
				if ($this->addAttackEffect($victim)) {
					Lemuria::Log()->debug($this->unit . ' is curious about ' . $victim . ' and has been provoked to attack.');
				} else {
					Lemuria::Log()->debug($this->unit . ' got curious about ' . $victim . '.');
				}
			}
		}
		return $this;
	}

	protected function getVictim(Fleet $big, Fleet $small): ?Vessel {
		foreach ($big as $vessel) {
			if (randChance(self::CURIOSITY_BIG)) {
				return $vessel;
			}
		}
		foreach ($small as $vessel) {
			if (randChance(self::CURIOSITY_SMALL)) {
				return $vessel;
			}
		}
		return null;
	}

	protected function getAttackEffect(): ?AttackOnVessel {
		$effect   = new AttackOnVessel(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($this->unit));
		return $existing instanceof AttackOnVessel ? $existing : null;
	}

	protected function addAttackEffect(Vessel $vessel): bool {
		if ($this->isProvokedBy($vessel)) {
			$effect   = new AttackOnVessel(State::getInstance());
			$existing = Lemuria::Score()->find($effect->setUnit($this->unit));
			if ($existing instanceof AttackOnVessel) {
				$effect = $existing;
			}
			$effect->setVessel($vessel);
			return true;
		}
		return false;
	}

	protected function isProvokedBy(Vessel $vessel): bool {
		$chance        = 1.0;
		$ship          = $vessel->Ship();
		$payload       = $ship->Payload();
		$payloadFactor = sqrt(($payload - $vessel->Space()) / $payload);
		$sailing       = $ship->Crew();
		$navigation    = self::createTalent(Navigation::class);
		$knowledge     = 0;
		foreach ($vessel->Passengers() as $unit) {
			$calculus = new Calculus($unit);
			$knowledge += $unit->Size() * $calculus->knowledge($navigation)->Level();
		}
		$sailingFactor = 1.0 / max(1.0, sqrt($knowledge / $sailing));
		$chance        = min(self::MAX_PROVOKE, $sailingFactor * $payloadFactor * $chance);
		$percent       = (int)round(100.0 * $chance);
		Lemuria::Log()->debug($this->unit . ' met vessel ' . $vessel . ' with provoke chance of ' . $percent . ' %.');
		return randChance($chance);
	}
}
