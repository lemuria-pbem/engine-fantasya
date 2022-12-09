<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class SupportCharityMessage extends SupportPayMessage
{
	protected Result $result = Result::EVENT;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' receives ' . $this->support . ' for support from unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
