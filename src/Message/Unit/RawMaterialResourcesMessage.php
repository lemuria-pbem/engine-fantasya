<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RawMaterialResourcesMessage extends MaterialResourcesMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find any ' . $this->material . '.';
	}
}
