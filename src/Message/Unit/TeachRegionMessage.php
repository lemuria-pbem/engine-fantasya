<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class TeachRegionMessage extends TeachStudentMessage
{
	protected string $level = Message::FAILURE;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->student . ': Not in our region.';
	}
}
