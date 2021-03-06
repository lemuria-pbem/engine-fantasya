<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ConstructionUnableMessage extends ConstructionCreateMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not skilled enought to create a new ' . $this->building . '.';
	}
}
