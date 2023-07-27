<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelAlternativeNavigableMessage extends TravelAlternativeMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has chosen alternative direction ' . $this->alternative . ' because there is land in ' . $this->direction . '.';
	}
}
