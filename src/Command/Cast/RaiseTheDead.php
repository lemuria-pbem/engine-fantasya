<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\ControlEffect;
use Lemuria\Engine\Fantasya\Effect\DeceasedPeasants;
use Lemuria\Engine\Fantasya\Effect\RaiseTheDeadEffect;
use Lemuria\Engine\Fantasya\Effect\RestInPeaceEffect;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\RaiseTheDeadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\RaiseTheDeadPeaceMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

final class RaiseTheDead extends AbstractCast
{
	use BuilderTrait;
	use MessageTrait;

	private const int ZOMBIES = 10;

	private int $zombies;

	public function cast(): void {
		$aura = $this->cast->Aura();
		if ($aura > 0) {
			$unit = $this->cast->Unit();
			$unit->Aura()->consume($aura);
			$region = $unit->Region();
			if ($this->isRestInPeaceActiveIn($region)) {
				$this->message(RaiseTheDeadPeaceMessage::class, $unit)->e($region);
				return;
			}

			$this->zombies = $this->cast->Level() * self::ZOMBIES;
			$this->raiseStarvedPeasants($region, $unit);
			$this->addRaiseEffectForCombat($region, $unit);
		}
	}

	private function isRestInPeaceActiveIn(Region $region): bool {
		$effect = new RestInPeaceEffect(State::getInstance());
		return (bool)Lemuria::Score()->find($effect->setRegion($region));
	}

	private function raiseStarvedPeasants(Region $region, Unit $unit): void {
		$state = State::getInstance();
		if ($this->zombies > 0) {
			$effect   = new DeceasedPeasants($state);
			$existing = Lemuria::Score()->find($effect->setRegion($region));
			if ($existing instanceof DeceasedPeasants) {
				$peasants = $existing->Peasants();
				$size     = min($this->zombies, $peasants);
				if ($peasants > 0) {
					$race    = self::createRace(Zombie::class);
					$party   = $state->getTurnOptions()->Finder()->Party()->findByRace($race);
					$create  = new Create($party, $region);
					$gang    = new Gang($race, $size);
					$control = $this->cast->Spell()->Aura() / self::ZOMBIES;
					foreach ($create->add($gang)->act()->getUnits() as $zombies) {
						$this->addControlEffect($state, $unit, $control, $zombies);
						$this->message(RaiseTheDeadMessage::class, $unit)->e($zombies)->i($gang);
					}
					$this->zombies -= $size;
				}
			}
		}
	}

	private function addControlEffect(State $state, Unit $summoner, float $aura, Unit $zombies): void {
		$effect = new ControlEffect($state);
		Lemuria::Score()->add($effect->setUnit($zombies)->setAura($aura)->setSummoner($summoner));
	}

	private function addRaiseEffectForCombat($region, $unit): void {
		if ($this->zombies > 0) {
			$effect   = new RaiseTheDeadEffect(State::getInstance());
			$existing = Lemuria::Score()->find($effect->setRegion($region));
			if ($existing instanceof RaiseTheDeadEffect) {
				if ($this->zombies > $existing->Raise()) {
					$existing->setSummoner($unit)->setRaise($this->zombies);
					Lemuria::Log()->debug($unit . ' wins Raise The Dead against ' . $existing->Summoner() . '.');
				} else {
					Lemuria::Log()->debug($unit . ' loses Raise The Dead to ' . $existing->Summoner() . '.');
				}
			} else {
				Lemuria::Score()->add($effect->setSummoner($unit)->setRaise($this->zombies));
				Lemuria::Log()->debug($unit . ' prepares Raise The Dead for ' . $this->zombies . ' fallen warriors.');
			}
		}
	}
}
