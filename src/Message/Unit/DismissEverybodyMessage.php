<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class DismissEverybodyMessage extends DismissEverythingMessage
{
	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' dismisses all persons to the peasants of region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
