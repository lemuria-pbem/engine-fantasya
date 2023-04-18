<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class AttackInBuildingMessage extends AttackOwnUnitMessage
{
	protected Singleton $building;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot attack unit ' . $this->unit . ' in a ' . $this->building . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->building = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'building') ?? parent::getTranslation($name);
	}
}
