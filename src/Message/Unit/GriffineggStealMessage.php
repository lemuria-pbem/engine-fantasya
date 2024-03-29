<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GriffineggStealMessage extends GriffineggOnlyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' steals ' . $this->eggs . '.';
	}
}
