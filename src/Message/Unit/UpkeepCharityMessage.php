<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;

class UpkeepCharityMessage extends UpkeepPayMessage
{
	public const UNIT = 'unit';

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' receives ' . $this->upkeep . ' from unit ' . $this->unit . ' for upkeep of construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get(self::UNIT);
	}
}
