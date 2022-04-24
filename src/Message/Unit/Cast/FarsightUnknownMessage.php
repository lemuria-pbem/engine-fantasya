<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class FarsightUnknownMessage extends AbstractCastMessage
{
	protected string $level = Message::FAILURE;

	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot cast Farsight on unknown region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
