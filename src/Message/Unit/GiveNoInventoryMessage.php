<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class GiveNoInventoryMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Singleton $gift;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no ' . $this->gift . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->gift = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'gift') ?? parent::getTranslation($name);
	}
}
