<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Effect\TalentEffect;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\EagleEyeMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Modification;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

final class EagleEye extends AbstractCast
{
	use BuilderTrait;

	private const RATE = 2.0;

	public function cast(): void {
		$unit   = $this->cast->Unit();
		$levels = $this->cast->Level();
		if ($levels > 0) {
			$unit->Aura()->consume($this->cast->Aura());
			$perception   = self::createTalent(Perception::class);
			$modification = new Modification($perception, $levels);
			$this->getEffect($unit)->Modifications()->add($modification);
			$calculus  = new Calculus($unit);
			$knowledge = $calculus->knowledge($perception)->Level();
			$this->message(EagleEyeMessage::class, $unit)->p($knowledge);
		}
	}

	private function getEffect(Unit $unit): TalentEffect {
		$effect   = new TalentEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($unit));
		if ($existing instanceof TalentEffect) {
			return $existing;
		}
		Lemuria::Score()->add($effect);
		return $effect;
	}
}
