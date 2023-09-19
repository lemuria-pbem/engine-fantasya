<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class TravelBlockedInMessage extends TravelGuardedRegionMessage
{
	protected string $direction;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' was blocked by the guards of party ' . $this->party . ' travelling ' . $this->direction. '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->direction($name, useFullName: true) ?? parent::getTranslation($name);
	}
}
