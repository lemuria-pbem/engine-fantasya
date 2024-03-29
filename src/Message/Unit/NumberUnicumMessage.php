<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class NumberUnicumMessage extends NumberUnicumUsedMessage
{
	protected Result $result = Result::Success;

	protected Singleton $composition;

	protected function create(): string {
		return 'New ID of unicum ' . $this->oldId . ' is ' . $this->newId . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->composition = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'composition', 2) ?? parent::getTranslation($name);
	}
}
