<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class AttackMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $section = Section::BATTLE;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' attacks unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
