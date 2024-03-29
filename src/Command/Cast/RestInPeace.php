<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\RestInPeaceEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\RestInPeaceMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class RestInPeace extends AbstractCast
{
	use MessageTrait;

	public function cast(): void {
		$aura = $this->cast->Aura();
		if ($aura > 0) {
			$unit = $this->cast->Unit();
			$unit->Aura()->consume($aura);
			$region   = $unit->Region();
			$effect   = new RestInPeaceEffect(State::getInstance());
			$existing = Lemuria::Score()->find($effect->setRegion($region));
			if (!$existing) {
				Lemuria::Score()->add($effect);
				$this->message(RestInPeaceMessage::class, $region);
			}
		}
	}
}
