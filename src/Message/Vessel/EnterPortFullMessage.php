<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class EnterPortFullMessage extends AbstractVesselMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::MOVEMENT;

	protected Id $port;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' cannot enter port ' . $this->port . ' - no free space.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->port = $message->get();
	}
}
