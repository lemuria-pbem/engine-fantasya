<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Factory\ActionTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Apply\WoundshutDamageMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Apply\WoundshutFullMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Apply\WoundshutMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Apply\WoundshutNoneMessage;
use Lemuria\Model\Fantasya\Commodity\Potion\Woundshut as Potion;

final class Woundshut extends AbstractUnitApply
{
	use ActionTrait;

	public function apply(): int {
		$used = $this->heal($this->apply->Count());
		$this->getEffect()->setCount($used);
		return $used;
	}

	private function heal(int $amount): int {
		$unit      = $this->apply->Unit();
		$hitpoints = $unit->Size() * $unit->Race()->Hitpoints();
		$health    = $unit->Health() * $hitpoints;
		$damage    = $hitpoints - $health;
		$this->message(WoundshutDamageMessage::class, $unit)->p($damage);

		if ($damage > 0) {
			$healing = $amount * Potion::HITPOINTS;
			if ($damage > $healing) {
				$this->message(WoundshutMessage::class, $unit);
				return $amount;
			} else {
				$this->message(WoundshutFullMessage::class, $unit);
				return (int)ceil($damage / Potion::HITPOINTS);
			}
		}
		$this->message(WoundshutNoneMessage::class, $unit);
		return 0;
	}
}
