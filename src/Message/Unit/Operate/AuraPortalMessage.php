<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message\Result;

class AuraPortalMessage extends AbstractOperateMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is filled up with aura.';
	}
}
