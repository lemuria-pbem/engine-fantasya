<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RawMaterialNoDemandMessage extends RawMaterialResourcesMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce ' . $this->material . ', no demand.';
	}
}
