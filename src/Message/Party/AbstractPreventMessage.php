<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

abstract class AbstractPreventMessage extends AbstractPartyMessage
{
	public const UNIT = 'unit';

	protected string $level = Message::SUCCESS;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' was prevented from ' . $this->createActivity() . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get(self::UNIT);
	}

	abstract protected function createActivity(): string;
}
