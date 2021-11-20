<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class PicketPocketNothingMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::PRODUCTION;

	protected Id $enemy;

	protected function create(): string {
		return 'Unit ' . $this->id . ' did not find any silver in the pockets of unit ' . $this->enemy . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->enemy = $message->get();
	}
}
