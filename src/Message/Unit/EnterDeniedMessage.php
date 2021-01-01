<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class EnterDeniedMessage extends EnterAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot enter the construction ' . $this->construction . ' without permission from owner.';
	}
}
