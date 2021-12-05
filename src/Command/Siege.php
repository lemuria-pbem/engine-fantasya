<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeLeaveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotFightingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotGuardingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotOurMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotOurselvesMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Combat;
use Lemuria\Model\Fantasya\Construction;

/**
 * Siege constructions.
 *
 * - BELAGERN <construction>
 */
final class Siege extends UnitCommand
{
	private ?Construction $construction = null;

	protected function initialize(): void {
		parent::initialize();
		if ($this->unit->BattleRow() <= Combat::BYSTANDER) {
			$this->message(SiegeNotFightingMessage::class);
			return;
		}
		if (!$this->unit->IsGuarding()) {
			$this->message(SiegeNotGuardingMessage::class);
			return;
		}
		if ($this->phrase->count() !== 1) {
			throw new InvalidCommandException($this);
		}

		$id     = Id::fromId($this->phrase->getParameter())->Id();
		$region = $this->unit->Region();
		foreach ($region->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Id()->Id() === $id) {
				$this->construction = $construction;
				break;
			}
		}
		if (!$this->construction) {
			$this->message(SiegeNotFoundMessage::class)->p($id);
			return;
		}
		$construction = $this->unit->Construction();
		if ($this->construction === $construction) {
			$this->message(SiegeNotOurselvesMessage::class);
			return;
		}
		if ($this->construction->Inhabitants()->Owner()?->Party() === $this->unit->Party()) {
			$this->message(SiegeNotOurMessage::class);
			return;
		}

		if ($construction) {
			$construction->Inhabitants()->remove($this->unit);
			$this->message(SiegeLeaveMessage::class);
		}
		$this->commitCommand($this);
	}

	protected function run(): void {
		if ($this->context->getTurnOptions()->IsSimulation()) {
			return;
		}
		//TODO Add siege for construction.
	}

	protected function commitCommand(UnitCommand $command): void {
		if ($this->construction) {

			parent::commitCommand($command);
		}
	}
}
