<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class WaterOfLifeOnlyMessage extends WaterOfLifeNoWoodMessage
{
	protected int $wood;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has only ' . $this->wood . ' wood to grow saplings with Water of Life.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->wood = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'wood') ?? parent::getTranslation($name);
	}
}
