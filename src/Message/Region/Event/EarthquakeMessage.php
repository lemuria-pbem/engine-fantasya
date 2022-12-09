<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message\Result;

class EarthquakeMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'Suddenly, in region ' . $this->id . ' the earth is shaking, and the buildings are damaged by an earthquake.';
	}
}
