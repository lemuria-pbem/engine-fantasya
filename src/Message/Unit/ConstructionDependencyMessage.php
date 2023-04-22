<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class ConstructionDependencyMessage extends AbstractUnitMessage
{
	public final const DEPENDENCY = 'dependency';

	protected Result $result = Result::Failure;

	protected Singleton $building;

	protected Singleton $dependency;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot build a ' . $this->building . ' - the party does not own a ' . $this->dependency . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->building   = $message->getSingleton();
		$this->dependency = $message->getSingleton('dependency');
	}

	protected function getTranslation(string $name): string {
		if ($name === 'building') {
			return $this->singleton($name, 'building');
		}
		if ($name === self::DEPENDENCY) {
			return $this->singleton($name, self::DEPENDENCY);
		}
		return parent::getTranslation($name);
	}
}
