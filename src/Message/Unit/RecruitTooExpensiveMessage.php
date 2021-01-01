<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

class RecruitTooExpensiveMessage extends RecruitLessMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' can pay ' . $this->size . ' recruits only.';
	}
}
