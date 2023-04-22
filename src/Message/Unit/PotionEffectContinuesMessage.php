<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class PotionEffectContinuesMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Singleton $potion;

	protected function create(): string {
		return 'The effect of ' . $this->potion . ' continues.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->potion = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'potion') ?? parent::getTranslation($name);
	}
}
