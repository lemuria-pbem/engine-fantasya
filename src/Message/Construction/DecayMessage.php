<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class DecayMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Event;

	protected Singleton $building;

	protected function create(): string {
		return 'The ravages of time let the ' . $this->building . ' ' . $this->id . ' decay.';
	}
}
