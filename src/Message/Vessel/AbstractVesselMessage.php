<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use Lemuria\Engine\Lemuria\Message\AbstractMessage;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Report;
use Lemuria\Model\Lemuria\Vessel;

abstract class AbstractVesselMessage extends AbstractMessage
{
	/**
	 * @return int
	 */
	public function Report(): int {
		return Report::VESSEL;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		$this->vessel = Vessel::get($message->get(Vessel::class));
	}
}
