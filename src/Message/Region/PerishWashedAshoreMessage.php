<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Message\Result;

class PerishWashedAshoreMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'In region ' . $this->id . ' a carcass is washed ashore.';
	}
}
