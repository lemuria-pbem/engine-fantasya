<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use Lemuria\Engine\Lemuria\Exception\MessageEntityException;
use Lemuria\Engine\Lemuria\Message\AbstractMessage;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Report;
use Lemuria\Model\Lemuria\Vessel;

abstract class AbstractVesselMessage extends AbstractMessage
{
	private ?Vessel $vessel = null;

	/**
	 * @return int
	 */
	public function Report(): int {
		return Report::VESSEL;
	}

	/**
	 * @return Vessel
	 */
	protected function Vessel(): Vessel {
		if (!$this->vessel) {
			throw new MessageEntityException(Vessel::class);
		}
		return $this->vessel;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getEntities(LemuriaMessage $message): void {
		$this->vessel = Vessel::get($message->get(Vessel::class));
	}
}
