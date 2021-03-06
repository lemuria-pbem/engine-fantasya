<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class PopulationNewMessage extends PopulationGrowthMessage
{
	protected function create(): string {
		return $this->peasants . ' migrate to region ' . $this->id . '.';
	}
}
