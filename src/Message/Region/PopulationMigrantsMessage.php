<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class PopulationMigrantsMessage extends PopulationGrowthMessage
{
	protected function create(): string {
		return $this->peasants . ' leave region ' . $this->id . '.';
	}
}
