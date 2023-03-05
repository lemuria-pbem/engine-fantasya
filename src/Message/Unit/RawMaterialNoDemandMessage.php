<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RawMaterialNoDemandMessage extends RawMaterialResourcesMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce ' . $this->material . ', no demand.';
	}

	protected function getTranslation(string $name): string {
		//TODO 1.3: Add accusative singular in commodity translations.
		return $this->commodity($name, 'material') ?? parent::getTranslation($name);
	}
}
