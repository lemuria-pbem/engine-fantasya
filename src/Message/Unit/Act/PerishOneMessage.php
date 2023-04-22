<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;
use Lemuria\Singleton;

class PerishOneMessage extends PerishMessage
{
	protected Id $unit;

	protected Singleton $race;

	protected function create(): string {
		return 'Unit ' . $this->id . ' (one ' . $this->race . ' of unit ' . $this->unit . ') stays behind as it will perish soon.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
		$this->race = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'race') ?? parent::getTranslation($name);
	}
}
