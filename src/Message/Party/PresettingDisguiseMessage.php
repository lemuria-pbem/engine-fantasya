<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class PresettingDisguiseMessage extends PresettingHideMessage
{
	protected function create(): string {
		return 'New units will disguise by default.';
	}
}
