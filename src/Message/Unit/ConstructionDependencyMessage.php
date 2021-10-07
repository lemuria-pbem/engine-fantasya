<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class ConstructionDependencyMessage extends AbstractUnitMessage
{
	public const DEPENDENCY = 'dependency';

	protected string $level = Message::FAILURE;

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
			return $this->building($name, 'building');
		}
		if ($name === self::DEPENDENCY) {
			return $this->building($name, self::DEPENDENCY);
		}
		return parent::getTranslation($name);
	}
}
