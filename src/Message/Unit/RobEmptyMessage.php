<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RobEmptyMessage extends RobSelfMessage
{
	protected function create(): string {
		return 'Empty unit ' . $this->id . ' cannot fight in any attack.';
	}
}
