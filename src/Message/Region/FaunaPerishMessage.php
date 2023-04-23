<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class FaunaPerishMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Economy;

	protected Singleton $animal;

	protected function create(): string {
		return 'In region ' . $this->id . ' one ' . $this->animal . ' perishes.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->animal = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'animal') ?? parent::getTranslation($name);
	}
}
