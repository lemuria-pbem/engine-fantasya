<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Party;

class TeachPartyMessage extends AbstractUnitMessage
{
	public const UNIT = 'unit';

	protected string $level = Message::FAILURE;

	protected int $section = Section::STUDY;

	protected Id $party;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->unit . ' of party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get(Party::class);
		$this->unit = $message->get(self::UNIT);
	}
}
