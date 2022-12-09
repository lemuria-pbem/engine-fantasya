<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

abstract class AbstractCastMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Magic;
}
