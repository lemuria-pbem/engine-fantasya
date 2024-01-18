<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class ThrowOutFromVesselMessage extends ThrowOutOwnMessage
{
	public final const string VESSEL = 'vessel';

	protected Result $result = Result::Event;

	protected Id $vessel;

	protected function create(): string {
		return 'Unit ' . $this->id . ' was forced to leave the vessel ' . $this->vessel . ' by unit ' . $this->unit . '.';
	}

	public function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = $message->get(self::VESSEL);
	}
}
