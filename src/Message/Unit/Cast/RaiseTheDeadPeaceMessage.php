<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class RaiseTheDeadPeaceMessage extends AbstractCastMessage
{
	protected Result $result = Result::Failure;

	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot raise the dead, because the dead rest in peace.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
