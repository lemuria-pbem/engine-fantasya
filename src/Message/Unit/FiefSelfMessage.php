<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class FiefSelfMessage extends FiefNoneMessage
{
	protected function create(): string {
		return 'We cannot hand over the realm to ourselves.';
	}
}
