<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RawMaterialGuardedMessage extends RawMaterialResourcesMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce ' . $this->material . ', the region is guarded.';
	}
}
