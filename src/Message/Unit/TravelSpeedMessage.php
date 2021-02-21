<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class TravelSpeedMessage extends AbstractUnitMessage
{
	public const WEIGHT = 'weight';

	protected int $speed;

	protected int $weight;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can move ' . $this->speed . ' regions (weight: ' . $this->weight . ').';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->speed  = $message->getParameter();
		$this->weight = $message->getParameter(self::WEIGHT);
	}
}
