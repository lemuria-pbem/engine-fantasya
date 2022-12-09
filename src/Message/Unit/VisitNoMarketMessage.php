<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class VisitNoMarketMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::MAIL;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot visit any merchant unit - there is no market here.';
	}
}
