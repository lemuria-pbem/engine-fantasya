<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class NumberVesselMessage extends AbstractVesselMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $oldId;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'New ID of vessel ' . $this->oldId . ' is ' . $this->id . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->oldId = new Id($message->getParameter());
	}
}
