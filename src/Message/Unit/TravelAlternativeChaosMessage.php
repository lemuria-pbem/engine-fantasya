<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelAlternativeChaosMessage extends TravelAlternativeMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has chosen alternative direction ' . $this->alternative . ' because there is chaos in ' . $this->direction . '.';
	}
}
