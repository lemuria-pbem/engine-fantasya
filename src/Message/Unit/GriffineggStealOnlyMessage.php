<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GriffineggStealOnlyMessage extends GriffineggOnlyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' could only steal ' . $this->eggs . '.';
	}
}
