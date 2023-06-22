<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class RealmRemoveMessage extends RealmAddMessage
{
	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' releases region ' . $this->region . ' from realm ' . $this->realm . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
