<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class NameRealmCentralMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected string $realm;

	protected function create(): string {
		return 'Unit ' . $this->id . ' must be in central region of realm ' . $this->realm . ' to rename it.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->realm = $message->getParameter();
	}
}
