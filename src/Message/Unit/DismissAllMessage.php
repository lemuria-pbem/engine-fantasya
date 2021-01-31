<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Singleton;

class DismissAllMessage extends DismissEverythingMessage
{
	protected Singleton $commodity;

	protected function create(): string {
		return 'Unit ' . $this->id . ' donates all ' . $this->commodity . ' to the peasants.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->commodity = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'commodity') ?? parent::getTranslation($name);
	}
}
