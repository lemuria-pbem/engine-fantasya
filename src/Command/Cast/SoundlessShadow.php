<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\SneakPastEffect;
use Lemuria\Engine\Fantasya\Effect\TalentEffect;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\SoundlessShadowMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Modification;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Unit;

final class SoundlessShadow extends AbstractCast
{
	use BuilderTrait;

	public function cast(): void {
		$unit   = $this->cast->Unit();
		$levels = $this->cast->Level();
		if ($levels > 0) {
			$unit->Aura()->consume($this->cast->Aura());
			$this->addSneakEffect($unit);
			$camouflage   = self::createTalent(Camouflage::class);
			$modification = new Modification($camouflage, $levels);
			$this->getTalentEffect($unit)->Modifications()->add($modification);
			$calculus  = new Calculus($unit);
			$knowledge = $calculus->knowledge($camouflage)->Level();
			$this->message(SoundlessShadowMessage::class, $unit)->p($knowledge);
		}
	}

	private function addSneakEffect(Unit $unit): void {
		$effect = new SneakPastEffect(State::getInstance());
		if (Lemuria::Score()->find($effect->setUnit($unit))) {
			return;
		}
		Lemuria::Score()->add($effect->addReassignment());
	}

	private function getTalentEffect(Unit $unit): TalentEffect {
		$effect   = new TalentEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($unit));
		if ($existing instanceof TalentEffect) {
			return $existing->addReassignment();
		}
		Lemuria::Score()->add($effect);
		return $effect->addReassignment();
	}
}
