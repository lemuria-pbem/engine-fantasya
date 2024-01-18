<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class ThrowOutOwnMessage extends ThrowOutNotMessage
{
	protected Id $unit;

	protected function create(): string {
		return 'We cannot throw out our own unit ' . $this->id . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
