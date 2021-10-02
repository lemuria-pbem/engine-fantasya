<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SawmillWoodUnusable extends RawMaterialResourcesMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce ' . $this->material . ' in the sawmill, not enough space in the cabin.';
	}
}
