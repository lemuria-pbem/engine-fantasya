<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\FarsightEffect;
use Lemuria\Engine\Fantasya\Effect\TalentEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\FarsightMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\FarsightTooFarMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\FarsightUnknownMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Modification;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

final class Farsight extends AbstractCast
{
	use BuilderTrait;
	use MessageTrait;

	public function cast(): void {
		$aura = $this->cast->Aura();
		if ($aura > 0) {
			$unit   = $this->cast->Unit();
			$region = $this->cast->Region();
			if (!$region) {
				$region = $unit->Region();
			}
			$distance = Lemuria::World()->getDistance($unit->Region(), $region);

			$chronicle = $unit->Party()->Chronicle();
			if (!$chronicle->has($region->Id())) {
				$this->message(FarsightUnknownMessage::class, $unit)->e($region);
				return;
			}

			$aura += $distance;
			if ($unit->Aura()->Aura() >= $aura) {
				$unit->Aura()->consume($aura);
				$chronicle->add($region);
				$this->addEffect($region, $unit);
				$this->message(FarsightMessage::class, $unit)->e($region);
			} else {
				$this->message(FarsightTooFarMessage::class, $unit)->e($region);
			}
		}
	}

	private function addEffect(Region $region, Unit $unit): void {
		$effect   = new FarsightEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setRegion($region));
		if (!$existing) {
			Lemuria::Score()->add($effect);
		} else {
			$effect = $existing;
		}

		$perception = self::createTalent(Perception::class);
		/** @var Ability $ability */
		$ability      = $unit->Knowledge()->offsetGet($perception);
		$modification = $unit->Race()->Modifications()->offsetGet($perception);
		if ($modification instanceof Modification) {
			$ability = $modification->getModified($ability);
		}
		$modification = $unit->Race()->TerrainEffect()->getEffect($region->Landscape(), $perception);
		if ($modification instanceof Modification) {
			$ability = $modification->getModified($ability);
		}
		$modification = $this->talentEffect($unit, $perception);
		if ($modification instanceof Modification) {
			$ability = $modification->getModified($ability);
		}
		$effect->addParty($unit->Party(), $ability->Level());
	}

	private function talentEffect(Unit $unit, Talent $talent): ?Modification {
		$effect   = new TalentEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($unit));
		if ($existing instanceof TalentEffect) {
			$modifications = $existing->Modifications();
			if (isset($modifications[$talent])) {
				return $modifications[$talent];
			}
		}
		return null;
	}
}
