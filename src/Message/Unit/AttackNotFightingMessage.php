<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackNotFightingMessage extends AttackSelfMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' will not fight in any attack.';
	}
}
