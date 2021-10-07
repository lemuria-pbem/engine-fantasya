<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class SawmillUnmaintainedMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::PRODUCTION;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce wood in an unmaintained sawmill.';
	}
}