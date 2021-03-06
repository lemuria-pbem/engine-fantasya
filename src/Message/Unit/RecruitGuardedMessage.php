<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RecruitGuardedMessage extends AbstractGuardedMessage
{
	protected function createActivity(): string {
		return 'recruit';
	}
}
