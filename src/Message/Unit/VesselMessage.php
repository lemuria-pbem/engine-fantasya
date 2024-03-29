<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class VesselMessage extends VesselOffBoardMessage
{
	protected Result $result = Result::Success;

	protected Singleton $ship;

	protected function create(): string {
		return 'Unit ' . $this->id . ' creates a new ' . $this->ship . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->ship = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'ship') ?? parent::getTranslation($name);
	}
}
