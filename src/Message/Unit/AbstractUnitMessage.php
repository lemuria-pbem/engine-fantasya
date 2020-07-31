<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\AbstractMessage;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Report;
use Lemuria\Model\Lemuria\Unit;

abstract class AbstractUnitMessage extends AbstractMessage
{
	/**
	 * @return int
	 */
	public function Report(): int {
		return Report::UNIT;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		$this->id = $message->get(Unit::class);
	}
}
