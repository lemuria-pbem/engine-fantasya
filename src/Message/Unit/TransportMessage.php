<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class TransportMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Economy;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will transport realm goods.';
	}
}
