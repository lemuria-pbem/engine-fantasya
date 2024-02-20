<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class ConstructionFarmMessage extends AbstractUnitMessage
{
	public final const string LANDSCAPE = 'landscape';

	protected Result $result = Result::Failure;

	protected Singleton $building;

	protected Singleton $landscape;

	protected function create(): string {
		return 'A ' . $this->building . ' cannot be built in a ' . $this->landscape . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->building  = $message->getSingleton();
		$this->landscape = $message->getSingleton(self::LANDSCAPE);
	}

	protected function getTranslation(string $name): string {
		if ($name === 'building') {
			return $this->singleton($name, 'building');
		}
		if ($name === self::LANDSCAPE) {
			return $this->singleton($name, self::LANDSCAPE);
		}
		return parent::getTranslation($name);
	}
}
