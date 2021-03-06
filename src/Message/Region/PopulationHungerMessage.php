<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message;

class PopulationHungerMessage extends PopulationGrowthMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->peasants . ' starve to death.';
	}
}
