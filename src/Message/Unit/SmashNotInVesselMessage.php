<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;

class SmashNotInVesselMessage extends AbstractUnitMessage
{
	protected string $level = LemuriaMessage::FAILURE;

	protected int $section = Section::PRODUCTION;

	protected function create(): string {
		return 'Unit ' . $this->id . ' must be inside the vessel to destroy it.';
	}
}
