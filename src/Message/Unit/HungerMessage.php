<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class HungerMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Debug;

	protected float $health;

	protected function create(): string {
		return 'Health of unit ' . $this->id . ' decreases to ' . $this->health . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->health = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'health') ?? parent::getTranslation($name);
	}
}
