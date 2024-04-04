<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party\Event;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class ColorOutOfSpaceDownInMessage extends ColorOutOfSpaceWellInMessage
{
	public final const DIRECTION = 'direction';

	protected string $direction;

	protected function create(): string {
		return 'In this last night of low voices in ' . $this->region . ' something special is happening: From ' .
			   'heaven high above in ' . $this->direction . ' suddenly a mighty pillar of strange pale violet light ' .
			   'rushes down. Some peasants nearby report that it pierces the surface and sinks down into the water. ' .
			   'For some time they watch a glow in the deep until it is vanished.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter(self::DIRECTION);
	}

	protected function getTranslation(string $name): string {
		return $this->direction($name, useFullName: true) ?? parent::getTranslation($name);
	}
}
