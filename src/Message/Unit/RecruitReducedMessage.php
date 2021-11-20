<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RecruitReducedMessage extends RecruitLessMessage
{
	protected function create(): string {
		return 'Number of recruits is reduced to ' . $this->size . ' due to free space in construction.';
	}
}
