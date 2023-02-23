<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Trespass;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Effect\UnpaidDemurrage;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Unit\BoardAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BoardDeniedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BoardUnpaidDemurrageMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionDebugMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselDebugMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BoardMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BoardNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselUnpaidDemurrageMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Vessel;

/**
 * A unit enters a vessel using the Board command.
 *
 * - BESTEIGEN <Vessel>
 * - BESTEIGEN Schiff <Vessel>
 */
final class Board extends UnitCommand
{
	use SiegeTrait;

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1 || $n > 2) {
			throw new InvalidCommandException($this);
		}
		if ($n === 2) {
			if (strtolower($this->phrase->getParameter()) !== 'schiff') {
				throw new InvalidCommandException($this);
			}
		}
		$id = $this->parseId($n);

		$vessel = $this->unit->Vessel();
		if ($vessel && $vessel->Id()->Id() === $id->Id()) {
			$this->message(BoardAlreadyMessage::class)->e($vessel);
			return;
		}
		if (!$this->unit->Region()->Fleet()->has($id)) {
			$this->message(BoardNotFoundMessage::class)->p($id->Id());
			return;
		}

		$newVessel = Vessel::get($id);
		if (!$this->hasPermission($newVessel->Passengers())) {
			$this->message(BoardDeniedMessage::class)->e($newVessel);
			return;
		}

		$effect = new UnpaidDemurrage(State::getInstance());
		if (Lemuria::Score()->find($effect->setVessel($newVessel))) {
			$this->message(BoardUnpaidDemurrageMessage::class)->e($newVessel);
		} else {
			if ($vessel) {
				if (Lemuria::Score()->find($effect->setVessel($vessel))) {
					$this->message(LeaveVesselUnpaidDemurrageMessage::class)->e($vessel);
					return;
				}
				$vessel->Passengers()->remove($this->unit);
				$this->message(LeaveVesselDebugMessage::class)->e($vessel);
			} else {
				$construction = $this->unit->Construction();
				if ($construction) {
					if ($this->initSiege($construction)->canEnterOrLeave($this->unit)) {
						$construction->Inhabitants()->remove($this->unit);
						$this->message(LeaveConstructionDebugMessage::class)->e($construction);
					} else {
						$this->message(LeaveSiegeMessage::class);
						return;
					}
				}
			}
			$newVessel->Passengers()->add($this->unit);
			$this->message(BoardMessage::class)->e($newVessel);
		}
	}

	protected function checkSize(): bool {
		return true;
	}
}
