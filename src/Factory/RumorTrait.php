<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect\Rumors;
use Lemuria\Engine\Fantasya\Factory\Model\Buzz;
use Lemuria\Engine\Fantasya\Message\Unit\RumorMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;

trait RumorTrait
{
	protected function createRumor(Unit $creator, string $rumor, ?Party $from = null): void {
		$effect   = new Rumors(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($creator));
		if ($existing instanceof Rumors) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
		}

		$buzz = new Buzz($rumor);
		$effect->Rumors()->add($buzz->setOrigin($from ?: $creator->Party()));
		$this->message(RumorMessage::class, $creator)->p($rumor);
	}
}
