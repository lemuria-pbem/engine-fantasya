<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Handover;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Message\Unit\GrantAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GrantFromOutsideMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GrantMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GrantNoConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GrantNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GrantNotInsideMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GrantTakeoverMessage;

/**
 * A unit who is owner of a construction or vessel grants another unit inside the command over it.
 *
 * - GIB <Unit> Kommando
 * - KOMMANDO <Unit>
 * - KOMMANDO
 */
final class Grant extends UnitCommand
{
	protected function run(): void {
		if ($this->phrase->count() <= 0) {
			$this->takeOver();
			return;
		}

		$i    = 1;
		$unit = $this->nextId($i);
		$id   = $unit->Id();

		$construction = $this->unit->Construction();
		if ($construction) {
			$inhabitants = $construction->Inhabitants();
			$owner       = $inhabitants->Owner();
			if ($unit === $owner) {
				$this->message(GrantAlreadyMessage::class);
				return;
			}
			if ($owner->Id()->Id() === $this->unit->Id()->Id()) {
				if ($inhabitants->has($id)) {
					$inhabitants->setOwner($unit);
					$this->message(GrantMessage::class)->e($unit);
					$this->message(GrantTakeoverMessage::class, $unit)->e($this->unit);
				} else {
					$this->message(GrantNotInsideMessage::class)->p($id->Id());
				}
			} else {
				$this->message(GrantNothingMessage::class);
			}
		} else {
			$this->message(GrantFromOutsideMessage::class);
		}
	}

	protected function takeOver(): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			$inhabitants = $construction->Inhabitants();
			$owner       = $inhabitants->Owner();
			if ($this->unit === $owner) {
				$this->message(GrantAlreadyMessage::class);
				return;
			}
			if ($owner->Party() === $this->unit->Party()) {
				$inhabitants->setOwner($this->unit);
				$this->message(GrantMessage::class, $owner)->e($this->unit);
				$this->message(GrantTakeoverMessage::class)->e($owner);
			} else {
				$this->message(GrantNothingMessage::class);
			}
		} else {
			$this->message(GrantNoConstructionMessage::class);
		}
	}
}
