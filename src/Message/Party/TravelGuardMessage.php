<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class TravelGuardMessage extends AbstractPartyMessage
{
	public const UNIT = 'unit';

	protected string $level = Message::SUCCESS;

	protected Id $region;

	protected Id $unit;

	protected function create(): string {
		return 'Our guards have stopped unit ' . $this->unit . ' in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
		$this->unit   = $message->get(self::UNIT);
	}
}
