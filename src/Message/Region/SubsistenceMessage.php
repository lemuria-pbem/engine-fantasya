<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Region;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Item;

class SubsistenceMessage extends AbstractRegionMessage
{
	public const SILVER = 'silver';

	protected string $level = Message::DEBUG;

	protected Item $peasants;

	protected Item $silver;

	protected int $wage;

	protected function create(): string {
		return 'In region ' . $this->id . ' (wage ' . $this->wage . ') ' . $this->peasants . ' earn ' . $this->silver . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->peasants = $message->getQuantity();
		$this->silver   = $message->getQuantity(self::SILVER);
		$this->wage     = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'peasants') {
			return $this->item($name, 'peasants');
		}
		return $this->item($name, 'silver') ?? parent::getTranslation($name);
	}
}
