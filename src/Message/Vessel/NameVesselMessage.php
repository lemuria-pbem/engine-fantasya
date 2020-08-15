<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class NameVesselMessage extends AbstractVesselMessage
{
	protected string $level = Message::SUCCESS;

	private string $name;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Vessel ' . $this->id . ' is now known as ' . $this->name . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter();
	}
}