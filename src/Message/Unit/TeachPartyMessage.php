<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;
use Lemuria\Model\Lemuria\Party;

class TeachPartyMessage extends AbstractUnitMessage
{
	public const UNIT = 'unit';

	protected string $level = Message::FAILURE;

	private Id $party;

	private Id $unit;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->unit . ' of party ' . $this->party . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get(Party::class);
		$this->unit = $message->get(self::UNIT);
	}
}
