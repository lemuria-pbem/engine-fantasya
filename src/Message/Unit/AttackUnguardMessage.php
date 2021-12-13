<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackUnguardMessage extends AttackSelfMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has fled and is not guarding anymore.';
	}
}
