<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class TheWildHuntMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Event;

	protected Id $unit;

	protected string $region;

	protected function create(): string {
		return 'One stormy night, our guards in ' . $this->region . ' are roused by a rumbling carriage, the ' .
				'pounding of many hooves and yelling screams. Shortly after, this fearful appearance rushes by, ' .
				'leaving your guards unable to move. Suddenly, they feel very tired and fall asleep. When they awake ' .
				'in the morning they find a box containing some vials and other things.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit   = $message->get();
		$this->region = $message->getParameter();
	}
}
