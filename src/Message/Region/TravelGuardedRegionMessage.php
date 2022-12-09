<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class TravelGuardedRegionMessage extends AbstractRegionMessage
{
	public final const PARTY = 'party';

	protected Result $result = Result::Event;

	protected Section $section = Section::Movement;

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
