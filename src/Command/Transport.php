<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\TransportMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TransportNotMessage;

/**
 * The transport command is used to define units that transport realm goods.
 *
 * TRANSPORTIEREN
 * TRANSPORTIEREN Nicht
 */
class Transport extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
			$this->setTransport();
		} elseif ($n === 1 && strtolower($this->phrase->getParameter()) === 'nicht') {
			$this->unsetTransport();
		} else {
			throw new InvalidCommandException($this);
		}
	}

	protected function setTransport(): void {
		$this->unit->setIsTransporting(true);
		$this->message(TransportMessage::class);
	}

	protected function unsetTransport(): void {
		$this->unit->setIsTransporting(false);
		$this->message(TransportNotMessage::class);
	}
}
