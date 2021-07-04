<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class AuraTransferRejectedMessage extends AuraTransferFailedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->unit . ' wanted to transfer Aura to us.';
	}
}
