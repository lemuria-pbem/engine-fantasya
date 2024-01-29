<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class VisitRumorMessage extends VisitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has heard a rumor from unit ' . $this->sender . ': ' . $this->message;
	}
}
