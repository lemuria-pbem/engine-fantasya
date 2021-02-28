<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Region;

class PopulationMigrantsMessage extends PopulationGrowthMessage
{
	protected function create(): string {
		return $this->peasants . ' leave region ' . $this->id . '.';
	}
}
