<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\Daydream as DaydreamEffect;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\DaydreamConcentrateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\DaydreamGuardMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\DaydreamLevelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\DaydreamMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\DaydreamOnlyMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class Daydream extends AbstractCast
{
	public function cast(): void {
		$unit   = $this->cast->Unit();
		$target = $this->cast->Target();
		$demand = $this->cast->Level();
		$size   = $target->Size();
		$needed = min($unit->Aura()->Aura(), $size * $demand);
		$level  = (int)floor($needed / $size);
		if ($level <= 0) {
			$this->message(DaydreamLevelMessage::class, $unit)->e($target)->p($needed);
			return;
		}

		$unit->Aura()->consume($needed);
		if ($target->IsGuarding()) {
			$target->setIsGuarding(false);
			$this->message(DaydreamGuardMessage::class, $target);
		} else {
			$this->message(DaydreamConcentrateMessage::class, $target);
		}

		$effect   = new DaydreamEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($target));
		if ($existing instanceof DaydreamEffect) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
		}
		$effect->setLevel($effect->Level() + $level);

		if ($level < $demand) {
			$this->message(DaydreamOnlyMessage::class, $unit)->e($target)->p($level);
		} else {
			$this->message(DaydreamMessage::class, $unit)->e($target)->p($level);
		}
	}
}
