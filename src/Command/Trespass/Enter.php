<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Trespass;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\FreeSpaceTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Unit\EnterAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EnterDeniedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EnterForbiddenMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EnterMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EnterNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EnterSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EnterTooLargeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionDebugMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselDebugMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Building\Signpost;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Relation;

/**
 * A unit enters a construction using the Enter command.
 *
 * - BETRETEN <Construction>
 */
final class Enter extends UnitCommand
{
	use FreeSpaceTrait;
	use SiegeTrait;

	private const FORBIDDEN = [Signpost::class];

	protected function run(): void {
		if ($this->phrase->count() < 1) {
			throw new InvalidCommandException($this);
		}
		$id = Id::fromId($this->phrase->getParameter(0));

		$construction = $this->unit->Construction();
		if ($construction && $construction->Id()->Id() === $id->Id()) {
			$this->message(EnterAlreadyMessage::class)->e($construction);
			return;
		}
		if (!$this->unit->Region()->Estate()->has($id)) {
			$this->message(EnterNotFoundMessage::class)->p($id->Id());
			return;
		}

		$newConstruction = Construction::get($id);
		$building        = $newConstruction->Building();
		if (isset(self::FORBIDDEN[$building::class])) {
			$this->message(EnterForbiddenMessage::class)->s($building);
			return;
		}

		if (!$this->initSiege($newConstruction)->canEnterOrLeave($this->unit)) {
			$this->message(EnterSiegeMessage::class)->e($newConstruction);
			return;
		}
		if ($this->isTooSmall($newConstruction, $this->unit)) {
			$this->message(EnterTooLargeMessage::class)->e($newConstruction);
			return;
		}
		if (!$this->checkPermission($newConstruction)) {
			$this->message(EnterDeniedMessage::class)->e($newConstruction);
			return;
		}

		if ($construction) {
			if ($this->initSiege($construction)->canEnterOrLeave($this->unit)) {
				$construction->Inhabitants()->remove($this->unit);
				$this->message(LeaveConstructionDebugMessage::class)->e($construction);
			} else {
				$this->message(LeaveSiegeMessage::class);
				return;
			}
		} else {
			$vessel = $this->unit->Vessel();
			if ($vessel) {
				$vessel->Passengers()->remove($this->unit);
				$this->message(LeaveVesselDebugMessage::class)->e($vessel);
			}
		}
		$newConstruction->Inhabitants()->add($this->unit);
		$this->message(EnterMessage::class)->e($newConstruction);
	}

	#[Pure] protected function checkSize(): bool {
		return true;
	}

	private function checkPermission(Construction $construction): bool {
		$owner = $construction->Inhabitants()->Owner();
		if ($owner) {
			$ownerParty = $owner->Party();
			if ($ownerParty !== $this->unit->Party()) {
				if ($this->context->getTurnOptions()->IsSimulation() || !$ownerParty->Diplomacy()->has(Relation::ENTER, $this->unit)) {
					return false;
				}
			}
		}
		return true;
	}
}
