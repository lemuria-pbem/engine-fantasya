<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

abstract class AbstractPreventMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected Section $section = Section::ECONOMY;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' was prevented from ' . $this->createActivity() . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}

	abstract protected function createActivity(): string;
}
