<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class UnmaintainedMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Economy;

	protected function create(): string {
		return 'Construction ' . $this->id . ' cannot be used as it was not maintained.';
	}
}
