<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class TaxNoCollectorsMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Debug;

	protected Section $section = Section::Production;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no tax collectors that could enforce tax payment.';
	}
}
