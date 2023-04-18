<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;
use Lemuria\Singleton;

class LoseUnicumMessage extends LoseEverythingMessage
{
	protected Id $unicum;

	protected Singleton $composition;

	protected function create(): string {
		return 'Unit ' . $this->id . ' throws the ' . $this->composition . ' ' . $this->unicum . ' away.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum = $message->get();
		$this->composition = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'composition') ?? parent::getTranslation($name);
	}
}
