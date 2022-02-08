<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class TravelGuardedMessage extends TravelRegionMessage
{
	public const GUARD = 'guard';

	protected string $level = Message::FAILURE;

	protected Id $guard;

	protected function create(): string {
		return 'Unit ' . $this->id . ' was stopped in region ' . $this->region . ' by the guards of party ' . $this->guard . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->guard = $message->get(self::GUARD);
	}
}
