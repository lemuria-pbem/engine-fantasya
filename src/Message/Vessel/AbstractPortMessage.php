<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

abstract class AbstractPortMessage extends AbstractVesselMessage
{
	protected string $level = Message::SUCCESS;

	protected Section $section = Section::MOVEMENT;

	protected Id $port;

	protected string $type;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' enters ' . $this->type . ' port ' . $this->port . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->port = $message->get();
	}
}
