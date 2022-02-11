<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

class SpellbookReadEmptyMessage extends ScrollReadEmptyMessage
{
	protected function create(): string {
		return 'The ' . $this->composition . ' ' . $this->unicum . ' is empty.';
	}
}
