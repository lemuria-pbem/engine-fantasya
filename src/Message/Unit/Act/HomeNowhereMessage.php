<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class HomeNowhereMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::MOVEMENT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is desperate because it will never make a home.';
	}
}
