<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class FaunaHungerMessage extends FaunaGrowthMessage
{
	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->animals . ' starve to death.';
	}
}
