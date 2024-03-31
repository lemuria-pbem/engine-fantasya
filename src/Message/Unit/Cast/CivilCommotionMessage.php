<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class CivilCommotionMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Economy;

	protected function create(): string {
		return 'In region ' . $this->id . ' the peasants start a riot.';
	}
}
