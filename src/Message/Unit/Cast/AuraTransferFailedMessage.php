<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class AuraTransferFailedMessage extends AuraTransferImpossibleMessage
{
	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot transfer any Aura to unit ' . $this->unit . '. The recipient refuses to accept Aura from us.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
