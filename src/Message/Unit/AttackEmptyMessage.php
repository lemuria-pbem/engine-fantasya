<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackEmptyMessage extends AttackSelfMessage
{
	protected function create(): string {
		return 'Empty unit ' . $this->id . ' cannot fight in any attack.';
	}
}
