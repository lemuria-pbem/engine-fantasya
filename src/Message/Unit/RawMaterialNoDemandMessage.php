<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class RawMaterialNoDemandMessage extends RawMaterialResourcesMessage
{
	protected Result $result = Result::Debug;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce ' . $this->material . ', no demand.';
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'material') ?? parent::getTranslation($name);
	}
}
