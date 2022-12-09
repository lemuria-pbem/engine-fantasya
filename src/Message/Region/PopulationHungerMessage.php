<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message\Result;

class PopulationHungerMessage extends PopulationGrowthMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->peasants . ' starve to death.';
	}
}
