<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GatherNotMessage extends GatherMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' will not gather loot.';
	}
}
