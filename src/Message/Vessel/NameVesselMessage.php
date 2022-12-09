<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class NameVesselMessage extends AbstractVesselMessage
{
	protected Result $result = Result::Success;

	protected string $name;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is now known as ' . $this->name . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter();
	}
}
