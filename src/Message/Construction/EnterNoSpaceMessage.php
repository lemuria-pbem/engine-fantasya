<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class EnterNoSpaceMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Movement;

	protected string $unit;

	protected Singleton $building;

	protected function create(): string {
		return 'There is no space for ' . $this->unit . ' on this ' . $this->building . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit     = $message->getParameter();
		$this->building = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'building') ?? parent::getTranslation($name);
	}
}
