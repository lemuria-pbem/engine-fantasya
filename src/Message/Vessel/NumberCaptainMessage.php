<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class NumberCaptainMessage extends AbstractVesselMessage
{
	protected Result $result = Result::Failure;

	protected Id $captain;

	protected function create(): string {
		return 'Unit ' . $this->captain . ' is not captain of vessel ' . $this->id . ' and thus cannot change its ID.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->captain = $message->get();
	}
}
