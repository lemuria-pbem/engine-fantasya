<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class ReserveAllMessage extends ReserveMessage
{
	protected Singleton $commodity;

	protected function create(): string {
		return 'Unit ' . $this->id . ' reserves all of ' . $this->commodity . '. ' . $this->reserve . ' is available in the pool.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->commodity = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'commodity', 1) ?? parent::getTranslation($name);
	}
}
