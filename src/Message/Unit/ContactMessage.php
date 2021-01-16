<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;

class ContactMessage extends AbstractUnitMessage
{
	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' contacts unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
