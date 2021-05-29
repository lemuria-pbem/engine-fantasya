<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Model\Fantasya\Commodity\Potion\Woundshut;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Singleton;

class WoundshutNoneMessage extends AbstractUnitMessage
{
	use BuilderTrait;

	protected string $level = Message::FAILURE;

	protected Singleton $woundshut;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does not need ' . $this->woundshut . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->woundshut = self::createCommodity(Woundshut::class);
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'woundshut') ?? parent::getTranslation($name);
	}
}