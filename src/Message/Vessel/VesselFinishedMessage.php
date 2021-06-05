<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class VesselFinishedMessage extends AbstractVesselMessage
{
	protected string $level = Message::SUCCESS;

	protected int $section = Section::PRODUCTION;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' has been finished.';
	}
}
