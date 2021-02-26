<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class HungerMessage extends SupportNothingMessage
{
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
