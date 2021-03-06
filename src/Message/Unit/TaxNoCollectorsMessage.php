<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class TaxNoCollectorsMessage extends AbstractUnitMessage
{
	protected string $level = Message::DEBUG;

	protected int $section = Section::PRODUCTION;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no tax collectors that could enforce tax payment.';
	}
}
