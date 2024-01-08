<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class ControlEffectOnlyMessage extends ControlEffectMessage
{
	private Item $gang;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only consume ' . $this->aura . ' aura to maintain control over ' . $this->gang . ' of unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->gang = $message->getGang();
	}
}
