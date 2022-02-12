<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class NameUnicumMessage extends NameUnitMessage
{
	protected Id $unicum;

	protected function create(): string {
		return 'Unicum ' . $this->unicum . ' has been renamed to ' . $this->name . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum = $message->get();
	}
}
