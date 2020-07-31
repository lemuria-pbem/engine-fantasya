<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Construction;

use Lemuria\Engine\Lemuria\Exception\MessageEntityException;
use Lemuria\Engine\Lemuria\Message\AbstractMessage;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Report;
use Lemuria\Model\Lemuria\Construction;

abstract class AbstractConstructionMessage extends AbstractMessage
{
	/**
	 * @return int
	 */
	public function Report(): int {
		return Report::CONSTRUCTION;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		$this->construction = Construction::get($message->get(Construction::class));
	}
}
