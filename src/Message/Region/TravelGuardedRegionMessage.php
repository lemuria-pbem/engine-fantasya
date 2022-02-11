<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class TravelGuardedRegionMessage extends AbstractRegionMessage
{
	public const PARTY = 'party';

	protected string $level = Message::EVENT;

	protected int $section = Section::MOVEMENT;

	protected Id $unit;

	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' was stopped by the guards of party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit  = $message->get();
		$this->party = $message->get(self::PARTY);
	}
}
