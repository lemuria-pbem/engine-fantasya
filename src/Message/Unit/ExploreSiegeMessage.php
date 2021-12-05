<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ExploreSiegeMessage extends ExploreExperienceMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is trapped in a sieged construction and cannot explore.';
	}
}
