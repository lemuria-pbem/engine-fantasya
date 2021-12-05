<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class SiegeDamageMessage extends SiegeMessage
{
	protected int $damage;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does ' . $this->damage . ' catapulting the construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->damage = $message->getParameter();
	}
}
