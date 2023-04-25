<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Item;

class ZombieInfectionMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected Item $peasants;

	protected function create(): string {
		return 'Zombies attack region ' . $this->id . ' and turn ' . $this->peasants . ' into new zombies.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->peasants = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'peasants') ?? parent::getTranslation($name);
	}
}
