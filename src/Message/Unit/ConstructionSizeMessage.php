<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ConstructionSizeMessage extends ConstructionCreateMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot build the ' . $this->building . ' further.';
	}
}
