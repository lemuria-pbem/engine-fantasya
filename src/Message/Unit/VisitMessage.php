<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class VisitMessage extends VisitRumorMessage
{
	protected function create(): string {
		return 'Unit ' . $this->unit . ' says: „' . $this->rumor . '“';
	}
}
