<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class PresettingNoDisguiseMessage extends PresettingHideMessage
{
	protected function create(): string {
		return 'New units will not disguise by default.';
	}
}
