<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;
use Lemuria\Singleton;

class RawMaterialOutputMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Production;

	protected Item $output;

	protected Singleton $talent;

	protected function create(): string {
		return 'Unit ' . $this->id . ' produces ' . $this->output . ' with ' . $this->talent . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->output = $message->getQuantity();
		$this->talent = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		$output = $this->item($name, 'output');
		if ($output) {
			return $output;
		}
		$talent = $this->talent($name, 'talent');
		if ($talent) {
			return $talent;
		}
		return parent::getTranslation($name);
	}
}
