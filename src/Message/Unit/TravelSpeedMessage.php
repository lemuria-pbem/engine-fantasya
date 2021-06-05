<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;

class TravelSpeedMessage extends AbstractUnitMessage
{
	public const WEIGHT = 'weight';

	protected int $section = Section::MOVEMENT;

	protected int $speed;

	protected int|float $weight;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can move ' . $this->speed . ' regions (weight: ' . $this->weight . ').';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->speed  = $message->getParameter();
		$weight       = $message->getParameter(self::WEIGHT);
		$calculated   = $weight / 100;
		$this->weight = $weight % 100 > 0 ? $calculated : (int)$calculated;
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'weight') ?? parent::getTranslation($name);
	}
}
