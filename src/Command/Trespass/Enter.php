<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Trespass;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\FreeSpaceTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
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
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Building\Market;
use Lemuria\Model\Fantasya\Building\Monument;
use Lemuria\Model\Fantasya\Building\Ruin;
use Lemuria\Model\Fantasya\Building\Signpost;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Reassignment;

/**
 * A unit enters a construction using the Enter command.
 *
 * - BETRETEN <Construction>
 * - BETRETEN Burg|Gebaeude|Gebäude <Construction>
 */
final class Enter extends UnitCommand implements Reassignment
{
	use FreeSpaceTrait;
	use ReassignTrait;
	use SiegeTrait;

	/**
	 * @type array<string>
	 */
	public final const array FORBIDDEN = [Monument::class, Ruin::class, Signpost::class];

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1 || $n > 2) {
			throw new InvalidCommandException($this);
		}
		if ($n === 2) {
			$what = mb_strtolower($this->phrase->getParameter());
			if (!in_array($what, ['burg', 'gebaeude', 'gebäude'])) {
				throw new InvalidCommandException($this);
			}
		}
		$id = $this->parseId($n);

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
		$agreement = $building instanceof Market ? Relation::MARKET : Relation::ENTER;
		if (!$this->hasPermission($newConstruction->Inhabitants(), $agreement)) {
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
		$this->useAdditionalSpace($newConstruction, $this->unit);
		$newConstruction->Inhabitants()->add($this->unit);
		$this->message(EnterMessage::class)->e($newConstruction);
	}

	protected function checkSize(): bool {
		return true;
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		return $domain === Domain::Construction;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->getReassignPhraseForParameter($this->phrase->count(), $old, $new);
	}
}
