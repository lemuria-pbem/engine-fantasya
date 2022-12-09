<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class WaterOfLifeMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::MAGIC;

	protected int $saplings;

	protected function create(): string {
		return 'Unit ' . $this->id . ' uses Water of Life to grow ' . $this->saplings . ' saplings.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->saplings = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'saplings') ?? parent::getTranslation($name);
	}
}
