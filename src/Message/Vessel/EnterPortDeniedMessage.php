<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

class EnterPortDeniedMessage extends EnterPortFullMessage
{
	protected function create(): string {
		return 'Vessel ' . $this->id . ' cannot enter foreign port ' . $this->port . '.';
	}
}
