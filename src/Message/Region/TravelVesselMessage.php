<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class TravelVesselMessage extends AbstractRegionMessage
{
	protected string $level = Message::SUCCESS;

	protected int $section = Section::MOVEMENT;

	protected string $vessel;

	protected function create(): string {
		return 'Vessel ' . $this->vessel . ' has travelled through region ' . $this->id . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = $message->getParameter();
	}
}
