<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class CorpseFungusMessage extends AbstractUnitMessage
{
	public final const string TURNED = 'turned';

	protected Result $result = Result::Event;

	protected Singleton $persons;

	protected Singleton $turned;

	protected function create(): string {
		return 'The ' . $this->persons . ' of unit ' . $this->id . ' are turned into ' . $this->turned . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->persons = $message->getSingleton();
		$this->turned  = $message->getSingleton(self::TURNED);
	}

	protected function getTranslation(string $name): string {
		if ($name === self::TURNED) {
			return $this->singleton($name, $name, 1);
		}
		return $this->singleton($name, 'persons', 1) ?? parent::getTranslation($name);
	}
}
