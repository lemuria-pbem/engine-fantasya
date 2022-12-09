<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class GrantMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Id $target;

	protected function create(): string {
		return 'Unit ' . $this->id . ' grants command to unit ' . $this->target . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->target = $message->get();
	}
}
