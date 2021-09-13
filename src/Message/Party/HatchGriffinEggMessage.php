<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class HatchGriffinEggMessage extends AbstractPartyMessage
{
	protected string $level = Message::EVENT;

	protected Id $unit;

	protected function create(): string {
		return 'A griffin hatches out of a griffin egg from unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
