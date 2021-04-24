<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class DescribeContinentUndoMessage extends DescribeContinentMessage
{
	protected function create(): string {
		return 'Party ' . $this->id . ' has discarded its description for the continent ' . $this->continent . '.';
	}
}
