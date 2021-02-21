<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;
use Lemuria\Singleton;

class TravelLandMessage extends TravelOverLandMessage
{
	protected Singleton $landscape;

	protected Id $region;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' cannot move ' . $this->direction . ' and land in ' . $this->landscape . ' ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->landscape = $message->getSingleton();
		$this->region    = $message->get();
	}

	protected function getTranslation(string $name): string {
		return $this->landscape($name, 'landscape') ?? parent::getTranslation($name);
	}
}
