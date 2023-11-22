<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class AuraTransferMessage extends AuraTransferReceivedMessage
{
	public final const string COST = 'cost';

	protected int $cost;

	protected function create(): string {
		return 'Unit ' . $this->id . ' consumed ' . $this->cost . ' Aura to transfer ' . $this->aura . ' Aura to unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->cost = $message->getParameter(self::COST);
	}
}
