<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\SortAfterMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortAfterInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortAfterOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortBeforeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortBeforeInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortFirstMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortFlipMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortFlipInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortLastInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortLastMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortNotInRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortWithForeignerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SortWithItselfMessage;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Reorder;

/**
 * The Sort command is used to reorder the units of one party in a region, a construction or a vessel.
 *
 * - SORTIEREN Anfang|Erste|Erster|Zuerst
 * - SORTIEREN Ende|Letzte|Letzter|Zuletzt
 * - SORTIEREN Vor <Unit>
 * - SORTIEREN Hinter|Nach <Unit>
 * - SORTIEREN Austausch|Austauschen|Auswechseln|Mit|Tausch|Tausche|Tauschen|Wechsel|Wechseln <Unit>
 */
final class Sort extends UnitCommand
{
	protected function run(): void {
		$with = null;
		$n    = $this->phrase->count();
		if ($n <= 0) {
			throw new InvalidCommandException($this, 'No sort details given.');
		}
		if ($n === 1) {
			$type = $this->phrase->getParameter();
		} else {
			$type = $this->phrase->getParameter(1);
			$i    = 2;
			$with = $this->nextId($i);
			if (!$with || $with->Party() !== $this->unit->Party() ) {
				$this->message(SortWithForeignerMessage::class)->e($with);
				return;
			}
			if ($with->Region() !== $this->unit->Region()) {
				$this->message(SortNotInRegionMessage::class)->e($with);
				return;
			}
		}

		switch (strtolower($type)) {
			case 'anfang' :
			case 'erste' :
			case 'erster' :
			case 'zuerst' :
				$this->sortAsFirst();
				break;
			case 'ende' :
			case 'letzte' :
			case 'letzter' :
			case 'zuletzt' :
				$this->sortAsLast();
				break;
			case 'vor' :
				$this->sortBefore($with);
				break;
			case 'hinter' :
			case 'nach' :
				$this->sortAfter($with);
				break;
			case 'austausch' :
			case 'austauschen' :
			case 'auswechseln' :
			case 'mit' :
			case 'tausch' :
			case 'tausche' :
			case 'tauschen' :
			case 'wechsel' :
			case 'wechseln' :
				$this->exchangeWith($with);
				break;
			default :
				throw new InvalidCommandException($this, 'Invalid type "' . $type . '".');
		}
	}

	/**
	 * Find first unit and sort this unit before it.
	 */
	private function sortAsFirst(): void {
		$residents = $this->unit->Region()->Residents();
		$first     = $residents[0]; /* @var Unit $first */
		$residents->reorder($this->unit, $first, Reorder::BEFORE);
		$this->message(SortFirstMessage::class);

		$construction = $this->unit->Construction();
		if ($construction) {
			$inhabitants = $construction->Inhabitants();
			$owner       = $inhabitants->Owner();
			if ($this->unit !== $owner) {
				$inhabitants->reorder($this->unit, $owner, Reorder::AFTER);
				$this->message(SortAfterOwnerMessage::class);
			}
		}
	}

	/**
	 * Find last unit and sort this unit after it.
	 */
	private function sortAsLast(): void {
		$residents = $this->unit->Region()->Residents();
		$l         = count($residents) - 1;
		$last      = $residents[$l]; /* @var Unit $last */
		$residents->reorder($this->unit, $last, Reorder::AFTER);
		$this->message(SortLastMessage::class);

		$construction = $this->unit->Construction();
		if ($construction) {
			$inhabitants = $construction->Inhabitants();
			if ($this->unit !== $inhabitants->Owner()) {
				$l    = count($inhabitants) - 1;
				$last = $inhabitants[$l];
				$inhabitants->reorder($this->unit, $last, Reorder::AFTER);
				$this->message(SortLastInConstructionMessage::class);
			}
		}

	}

	private function sortBefore(Unit $unit): void {
		if ($this->checkIdentity($unit)) {
			$residents = $this->unit->Region()->Residents();
			$residents->reorder($this->unit, $unit, Reorder::BEFORE);
		}
		$this->message(SortBeforeMessage::class)->e($unit);

		$construction = $this->unit->Construction();
		if ($construction && $unit->Construction() === $construction) {
			$inhabitants = $construction->Inhabitants();
			$owner       = $inhabitants->Owner();
			if ($this->unit !== $owner) {
				if ($unit === $owner) {
					$inhabitants->reorder($this->unit, $owner, Reorder::AFTER);
					$this->message(SortAfterOwnerMessage::class);
				} else {
					$inhabitants->reorder($this->unit, $unit, Reorder::BEFORE);
					$this->message(SortBeforeInConstructionMessage::class)->e($unit);
				}
			}
		}

	}

	private function sortAfter(Unit $unit): void {
		if ($this->checkIdentity($unit)) {
			$residents = $this->unit->Region()->Residents();
			$residents->reorder($this->unit, $unit, Reorder::AFTER);
			$this->message(SortAfterMessage::class)->e($unit);

			$construction = $this->unit->Construction();
			if ($construction && $unit->Construction() === $construction) {
				$inhabitants = $construction->Inhabitants();
				if ($this->unit !== $inhabitants->Owner()) {
					$inhabitants->reorder($this->unit, $unit, Reorder::AFTER);
					$this->message(SortAfterInConstructionMessage::class)->e($unit);
				}
			}

		}
	}

	private function exchangeWith(Unit $unit): void {
		if ($this->checkIdentity($unit)) {
			$residents = $this->unit->Region()->Residents();
			$residents->reorder($this->unit, $unit, Reorder::FLIP);
			$this->message(SortFlipMessage::class)->e($unit);

			$construction = $this->unit->Construction();
			if ($construction && $unit->Construction() === $construction) {
				$inhabitants = $construction->Inhabitants();
				$owner       = $inhabitants->Owner();
				if ($this->unit !== $owner) {
					if ($unit === $owner) {
						$inhabitants->reorder($this->unit, $owner, Reorder::AFTER);
						$this->message(SortAfterOwnerMessage::class);
					} else {
						$inhabitants->reorder($this->unit, $unit, Reorder::FLIP);
						$this->message(SortFlipInConstructionMessage::class)->e($unit);
					}
				}
			}
		}
	}

	private function checkIdentity(Unit $unit): bool {
		if ($unit === $this->unit) {
			$this->message(SortWithItselfMessage::class);
			return false;
		}
		return true;
	}
}
