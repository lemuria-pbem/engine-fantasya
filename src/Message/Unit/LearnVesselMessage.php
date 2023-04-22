<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class LearnVesselMessage extends LearnNotMessage
{
	public final const SHIP = 'ship';

	protected Singleton $ship;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot learn ' . $this->talent . ' on a ' . $this->ship . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->ship = $message->getSingleton(self::SHIP);
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, self::SHIP) ?? parent::getTranslation($name);
	}
}
