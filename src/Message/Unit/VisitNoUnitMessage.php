<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class VisitNoUnitMessage extends VisitNoMarketMessage
{
	protected string $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find unit ' . $this->unit . ' for visiting.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->getParameter();
	}
}
