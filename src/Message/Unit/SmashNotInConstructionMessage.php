<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;

class SmashNotInConstructionMessage extends AbstractUnitMessage
{
	protected string $level = LemuriaMessage::FAILURE;

	protected int $section = Section::PRODUCTION;

	protected function create(): string {
		return 'Unit ' . $this->id . ' must be inside the construction to destroy it.';
	}
}
