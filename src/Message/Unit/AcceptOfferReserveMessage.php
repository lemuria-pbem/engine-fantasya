<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class AcceptOfferReserveMessage extends AcceptOfferAmountMessage
{
	protected Singleton $commodity;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' has not enough ' . $this->commodity . ' left to fulfill the trade with ID ' . $this->trade . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->commodity = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'commodity', 1) ?? parent::getTranslation($name);
	}
}
