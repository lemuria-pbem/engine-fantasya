<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class NonAggressionPactMessage extends NonAggressionPactLastMessage
{
	protected int $rounds;

	protected function create(): string {
		return 'Your units are protected from attacks for ' . $this->rounds . ' rounds.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->rounds = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'rounds') ?? parent::getTranslation($name);
	}
}
