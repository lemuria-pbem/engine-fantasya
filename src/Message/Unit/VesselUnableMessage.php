<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class VesselUnableMessage extends VesselCreateMessage
{
	public final const TALENT = 'talent';

	protected Singleton $talent;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is not skilled enough in ' . $this->talent . ' to create a new ' . $this->ship . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton(self::TALENT);
	}

	protected function getTranslation(string $name): string {
		return $this->talent($name, self::TALENT) ?? parent::getTranslation($name);
	}
}
