<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
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
		if ($name === 'commodity') {
			return $this->translateSingleton($this->commodity, 1, Casus::Dative);
		}
		return parent::getTranslation($name);
	}
}
