<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class BreedingFullMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Production;

	protected Singleton $animal;

	protected function create(): string {
		return 'There is no more space on this farm to breed ' . $this->animal . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->animal = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'animal', 1) ?? parent::getTranslation($name);
	}
}
