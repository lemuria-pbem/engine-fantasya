<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GrantNotOnBoardMessage extends GrantNotInsideMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot grant command. Target unit ' . $this->target . ' is not on board.';
	}
}
