<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\Reliability;

class AuraTransferNoMagicianMessage extends AuraTransferFailedMessage
{
	protected Reliability $reliability = Reliability::Determined;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot transfer Aura to unit ' . $this->unit . ' which is not a magician.';
	}
}
