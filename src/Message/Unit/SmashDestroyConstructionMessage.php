<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SmashDestroyConstructionMessage extends SmashLeaveConstructionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has destroyed construction ' . $this->construction . '.';
	}
}
