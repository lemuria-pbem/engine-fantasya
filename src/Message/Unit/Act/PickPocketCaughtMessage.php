<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;

class PickPocketCaughtMessage extends AbstractUnitMessage
{
	protected Result $result = Result::EVENT;

	protected string $thief;

	protected function create(): string {
		return 'We caught unit ' . $this->thief . ' in an attempt to steal silver from us.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->thief = $message->getParameter();
	}
}
