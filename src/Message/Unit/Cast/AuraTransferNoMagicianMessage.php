<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class AuraTransferNoMagicianMessage extends AuraTransferFailedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot transfer Aura to unit ' . $this->unit . ' which is not a magician.';
	}
}
