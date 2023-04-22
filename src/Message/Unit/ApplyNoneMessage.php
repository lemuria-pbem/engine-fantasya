<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class ApplyNoneMessage extends ApplyAlreadyMessage
{
	protected Singleton $potion;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no ' . $this->potion . ' to apply.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->potion = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'potion') ?? parent::getTranslation($name);
	}
}
