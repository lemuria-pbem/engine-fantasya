<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class PresettingNoRepeatMessage extends PresettingRepeatMessage
{
	protected function create(): string {
		return 'New trades will not be repeated by default.';
	}
}
