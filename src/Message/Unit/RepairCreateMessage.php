<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RepairCreateMessage extends RawMaterialOutputMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' repairs ' . $this->output . ' with ' . $this->talent . '.';
	}
}
