<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class FeeNoneMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Economy;

	protected Singleton $building;

	protected function create(): string {
		return 'The fee for the ' . $this->building . ' ' . $this->id . ' has been suspended.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->building = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->building($name, 'building') ?? parent::getTranslation($name);
	}
}
