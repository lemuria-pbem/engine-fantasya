<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GuardRegionMessage extends GuardMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' will not block borders.';
	}
}
