<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class TeleportationMessage extends AbstractCastMessage
{
	protected Result $result = Result::SUCCESS;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' teleports unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
