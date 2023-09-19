<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class TravelBlockMessage extends TravelGuardMessage
{
	protected string $direction;

	protected function create(): string {
		return 'Our guards have blocked unit ' . $this->unit . ' in region ' . $this->region . ' travelling to ' . $this->direction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->direction($name, useFullName: true) ?? parent::getTranslation($name);
	}
}
