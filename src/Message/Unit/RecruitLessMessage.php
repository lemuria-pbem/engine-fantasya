<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

use Lemuria\Engine\Message;

class RecruitLessMessage extends RecruitMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can recruit ' . $this->size . ' peasants only.';
	}
}
