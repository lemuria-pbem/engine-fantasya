<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class VisitRumorMessage extends VisitNoRumorMessage
{
	protected string $rumor;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has heard a rumor from unit ' . $this->unit . ': ' . $this->rumor;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->rumor = $message->getParameter();
	}
}