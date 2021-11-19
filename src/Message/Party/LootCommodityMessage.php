<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class LootCommodityMessage extends LootNothingMessage
{
	protected Singleton $commodity;

	protected function create(): string {
		return 'We will pick all ' . $this->commodity . ' for loot.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->commodity = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'commodity', 1) ?? parent::getTranslation($name);
	}
}
