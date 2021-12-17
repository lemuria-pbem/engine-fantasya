<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Item;

class EnterPortDutyMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::ECONOMY;

	protected Item $duty;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has to pay ' . $this->duty . ' for duty to the harbour master.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->duty = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'duty') ?? parent::getTranslation($name);
	}
}
