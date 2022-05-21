<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\Airship as AirshipEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AirshipNotOnBoardMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;

final class Airship extends AbstractCast
{
	use BuilderTrait;
	use MessageTrait;

	public function cast(): void {
		$unit   = $this->cast->Unit();
		$vessel = $unit->Vessel();
		if (!$vessel) {
			$this->message(AirshipNotOnBoardMessage::class, $unit);
			return;
		}

		$effect   = new AirshipEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setVessel($vessel));
		if ($existing instanceof AirshipEffect) {
			$effect = $existing;
		}
		$effect->Mages()->add($unit);
		// Casting is done when effect is executed.
	}
}
