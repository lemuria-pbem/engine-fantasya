<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class CommodityUnmaintainedMessage extends CommodityResourcesMessage
{
	public const BUILDING = 'building';

	protected Singleton $building;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot create ' . $this->artifact . ' in an unmaintained ' . $this->building . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->building = $message->getSingleton(self::BUILDING);
	}

	protected function getTranslation(string $name): string {
		return $this->building($name, self::BUILDING) ?? parent::getTranslation($name);
	}
}
