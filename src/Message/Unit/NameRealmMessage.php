<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class NameRealmMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected string $name;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has renamed the realm to ' . $this->name . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter();
	}
}
