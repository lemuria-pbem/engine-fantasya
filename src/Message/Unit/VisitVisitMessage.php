<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class VisitVisitMessage extends VisitNoRumorMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' says hello to unit ' . $this->unit . ' and asks for news.';
	}
}
