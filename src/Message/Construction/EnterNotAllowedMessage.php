<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

class EnterNotAllowedMessage extends EnterNoSpaceMessage
{
	protected function create(): string {
		return $this->unit . ' is not allowed to enter this ' . $this->building . '.';
	}
}
