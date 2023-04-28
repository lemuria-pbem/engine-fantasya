<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;
use Lemuria\Item;

abstract class AbstractLootMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Id $environment;

	protected Item $loot;

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->environment = $message->get();
		$this->loot        = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'loot', casus: Casus::Adjective) ?? parent::getTranslation($name);
	}
}
