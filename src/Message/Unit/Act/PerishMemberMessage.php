<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

class PerishMemberMessage extends PerishMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' loses a member due to perishing.';
	}
}
