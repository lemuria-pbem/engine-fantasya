<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\FavorableWinds as FavorableWindsEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\FavorableWindsNoMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\FavorableWindsMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Vessel;

final class FavorableWinds extends AbstractCast
{
	use BuilderTrait;
	use MessageTrait;

	public function cast(): void {
		$unit   = $this->cast->Unit();
		$vessel = $unit->Vessel();
		if (!$vessel) {
			$this->message(FavorableWindsNoMessage::class, $unit);
			return;
		}
		$unit->Aura()->consume($this->cast->Aura());
		$this->addEffect($vessel);
		$this->message(FavorableWindsMessage::class, $vessel);
	}

	private function addEffect(Vessel $vessel): void {
		$effect   = new FavorableWindsEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setVessel($vessel));
		if (!$existing) {
			Lemuria::Score()->add($effect);
		}
	}
}
