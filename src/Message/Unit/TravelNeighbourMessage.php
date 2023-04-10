<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class TravelNeighbourMessage extends TravelRegionMessage
{
	protected string $direction;

	protected Singleton $landscape;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can move ' . $this->direction . ' to ' . $this->landscape . ' ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->landscape = $message->getSingleton();
		$this->direction = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'landscape') {
			return $this->landscape($name, 'landscape');
		}
		return $this->direction($name) ?? parent::getTranslation($name);
	}
}
