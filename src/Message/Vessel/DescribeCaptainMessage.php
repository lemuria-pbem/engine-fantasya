<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class DescribeCaptainMessage extends AbstractVesselMessage
{
	public const CAPTAIN = 'captain';

	protected string $level = Message::FAILURE;

	private Id $captain;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->captain . ' is not captain of vessel ' . $this->id . ' and thus cannot describe it.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->captain = $message->get(self::CAPTAIN);
	}
}
