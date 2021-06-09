<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class SmashRegainMessage extends AbstractUnitMessage
{
	protected int $section = Section::PRODUCTION;

	protected Item $regain;

	protected function create(): string {
		return 'Unit ' . $this->id . ' regains ' . $this->regain . ' from destroying.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->regain = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'regain') ?? parent::getTranslation($name);
	}
}
