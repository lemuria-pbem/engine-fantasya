<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Engine\Message\Result;

class TravelBlockedMessage extends TravelRegionMessage
{
	protected Result $result = Result::Failure;

	protected Reliability $reliability = Reliability::Unreliable;

	protected string $direction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' was blocked by guards in region ' . $this->region . ' travelling to ' . $this->direction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->direction($name, useFullName: true) ?? parent::getTranslation($name);
	}
}
