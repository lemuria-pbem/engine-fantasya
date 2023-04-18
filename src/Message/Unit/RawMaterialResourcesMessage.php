<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class RawMaterialResourcesMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Production;

	protected Singleton $material;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find any ' . $this->material . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->material = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'material') ?? parent::getTranslation($name);
	}
}
