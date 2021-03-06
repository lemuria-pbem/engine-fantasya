<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class PartyVisitMessage extends AbstractPartyMessage
{
	protected int $regions;

	protected function create(): string {
		return 'Party ' . $this->id . ' visits ' . $this->regions . ' regions.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->regions = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'regions') ?? parent::getTranslation($name);
	}
}
