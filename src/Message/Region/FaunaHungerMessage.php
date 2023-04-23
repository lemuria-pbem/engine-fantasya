<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message\Result;

class FaunaHungerMessage extends FaunaGrowthMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->animals . ' starve to death.';
	}
}
