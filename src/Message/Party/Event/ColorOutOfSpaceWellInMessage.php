<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party\Event;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\AbstractPartyMessage;
use Lemuria\Engine\Message\Result;

class ColorOutOfSpaceWellInMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Event;

	protected string $region;

	protected function create(): string {
		return 'In this last night of low voices in ' . $this->region . ' something special is happening: From ' .
			   'heaven high above suddenly a mighty pillar of strange pale violet light rushes down. Some peasants ' .
			   'nearby report that this outlandish light flows above the ground like a liquid until it reaches a ' .
			   'fountain, trickling down. For some time they watch a glow in the deep until it is vanished.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->getParameter();
	}
}
