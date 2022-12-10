<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Engine\Message;

class AuraTransferReceivedMessage extends AuraTransferFailedMessage
{
	protected string $level = Message::EVENT;

	protected Reliability $reliability = Reliability::Determined;

	protected int $aura;

	protected function create(): string {
		return 'Unit ' . $this->id . ' received ' . $this->aura . ' Aura from unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->aura = $message->getParameter();
	}
}
