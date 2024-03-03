<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Singleton;

class BuyNoneMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Production;

	protected Id $region;

	protected Singleton $goods;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot buy any ' . $this->goods . ' from the peasants in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
		$this->goods  = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'goods', 1) ?? parent::getTranslation($name);
	}
}
