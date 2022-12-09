<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

abstract class AbstractMarketUpdateMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Economy;

	protected Singleton $luxury;

	protected int $price;

	protected function create(): string {
		return 'Commerce in region ' . $this->id . ' has ' . $this->change() . ' the price for ' . $this->luxury . ' to ' . $this->price . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->luxury = $message->getSingleton();
		$this->price  = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'luxury') ?? parent::getTranslation($name);
	}

	abstract protected function change(): string;
}
