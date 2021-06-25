<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message;

class AuraTransferImpossibleMessage extends AbstractCastMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no Aura left for transfer.';
	}
}
