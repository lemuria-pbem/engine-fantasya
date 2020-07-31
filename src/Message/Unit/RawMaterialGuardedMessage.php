<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class RawMaterialGuardedMessage extends RawMaterialResourcesMessage
{
	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->Id() . ' cannot produce ' . $this->material . ', the region is guarded.';
	}
}
