<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class ThrowOutFromConstructionMessage extends ThrowOutOwnMessage
{
	public final const string CONSTRUCTION = 'construction';

	protected Result $result = Result::Event;

	protected Id $construction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' was forced to leave the construction ' . $this->construction . ' by unit ' . $this->unit . '.';
	}

	public function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get(self::CONSTRUCTION);
	}
}
