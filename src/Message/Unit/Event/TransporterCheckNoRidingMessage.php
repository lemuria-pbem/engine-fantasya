<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Event;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class TransporterCheckNoRidingMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Movement;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough knowledge in riding for transporting.';
	}
}
