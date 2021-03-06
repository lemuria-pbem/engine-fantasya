<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class BuyNoneMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::PRODUCTION;

	protected Singleton $goods;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot buy any ' . $this->goods . ' from the peasants.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->goods = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'goods') ?? parent::getTranslation($name);
	}
}
