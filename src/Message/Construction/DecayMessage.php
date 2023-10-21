<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class DecayMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Event;

	protected Singleton $building;

	protected function create(): string {
		return 'The ravages of time let the ' . $this->building . ' ' . $this->id . ' decay.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->building = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'building') ?? parent::getTranslation($name);
	}
}
