<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class TravelGuardMessage extends AbstractPartyMessage
{
	public final const UNIT = 'unit';

	protected Result $result = Result::Event;

	protected Section $section = Section::Movement;

	protected Id $region;

	protected Id $unit;

	protected function create(): string {
		return 'Our guards have stopped unit ' . $this->unit . ' in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
		$this->unit   = $message->get(self::UNIT);
	}
}
