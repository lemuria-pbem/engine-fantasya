<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RepairOnlyMessage extends RawMaterialOnlyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' can only repair ' . $this->output . ' with ' . $this->talent . '.';
	}
}
