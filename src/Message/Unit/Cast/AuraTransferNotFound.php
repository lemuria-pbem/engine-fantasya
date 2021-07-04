<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class AuraTransferNotFound extends AuraTransferFailedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot transfer Aura, target unit ' . $this->unit . ' is not here.';
	}
}
