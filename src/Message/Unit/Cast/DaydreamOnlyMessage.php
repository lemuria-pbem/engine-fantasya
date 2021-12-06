<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class DaydreamOnlyMessage extends DaydreamLevelMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' could only cast Daydream with level ' . $this->needed . ' on unit ' . $this->unit . '.';
	}
}
