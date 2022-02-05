<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class ReadNoCompositionMessage extends ReadNoUnicumMessage
{
	protected Singleton $composition;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no ' . $this->composition . ' with ID ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->composition = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->composition($name, 'composition') ?? parent::getTranslation($name);
	}
}
