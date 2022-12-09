<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class TeachPartyMessage extends AbstractUnitMessage
{
	public final const UNIT = 'unit';

	protected Result $result = Result::Failure;

	protected Section $section = Section::Study;

	protected Id $party;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->unit . ' of party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
		$this->unit  = $message->get(self::UNIT);
	}
}
