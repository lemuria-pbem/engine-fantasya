<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class BoardNotAllowedMessage extends AbstractVesselMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Movement;

	protected string $unit;

	protected function create(): string {
		return $this->unit . ' is not allowed to board this ship.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->getParameter();
	}
}
