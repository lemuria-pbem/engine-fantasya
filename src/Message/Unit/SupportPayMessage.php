<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class SupportPayMessage extends AbstractUnitMessage
{
	protected Item $support;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays ' . $this->support . ' support.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->support = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'support') ?? parent::getTranslation($name);
	}
}
