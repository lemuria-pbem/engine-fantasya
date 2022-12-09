<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class DaydreamLevelMessage extends AbstractCastMessage
{
	protected Result $result = Result::FAILURE;

	protected Id $unit;

	protected int $needed;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot cast Daydream on unit ' . $this->unit . ' - at least ' . $this->needed . ' Aura is needed.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit   = $message->get();
		$this->needed = $message->getParameter();
	}
}
