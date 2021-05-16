<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class WaterOfLifeMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $saplings;

	protected function create(): string {
		return 'Unit ' . $this->id . ' uses Water of Life to grow ' . $this->saplings . ' saplings.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->saplings = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'saplings') ?? parent::getTranslation($name);
	}
}
