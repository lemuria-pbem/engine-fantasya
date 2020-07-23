<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Exception\MessageEntityException;
use Lemuria\Engine\Lemuria\Message\AbstractMessage;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Report;
use Lemuria\Model\Lemuria\Unit;

abstract class AbstractUnitMessage extends AbstractMessage
{
	private ?Unit $unit = null;

	/**
	 * @return int
	 */
	public function Report(): int {
		return Report::UNIT;
	}

	/**
	 * @return Unit
	 */
	protected function Unit(): Unit {
		if (!$this->unit) {
			throw new MessageEntityException(Unit::class);
		}
		return $this->unit;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getEntities(LemuriaMessage $message): void {
		$this->unit = Unit::get($message->get(Unit::class));
	}
}
