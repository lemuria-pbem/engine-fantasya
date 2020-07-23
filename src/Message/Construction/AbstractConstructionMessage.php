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
	private ?Construction $construction = null;

	/**
	 * @return int
	 */
	public function Report(): int {
		return Report::CONSTRUCTION;
	}

	/**
	 * @return Construction
	 */
	protected function Construction(): Construction {
		if (!$this->construction) {
			throw new MessageEntityException(Construction::class);
		}
		return $this->construction;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getEntities(LemuriaMessage $message): void {
		$this->construction = Construction::get($message->get(Construction::class));
	}
}
