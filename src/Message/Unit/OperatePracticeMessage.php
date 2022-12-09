<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class OperatePracticeMessage extends OperateNoCompositionMessage
{
	public final const PRACTICE = 'practice';

	protected Result $result = Result::DEBUG;

	protected string $practice;

	protected function create(): string {
		return 'Unit ' . $this->id . ' practices ' . $this->practice . ' with ' . $this->composition . ' ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->practice = $message->getParameter(self::PRACTICE);
	}
}
