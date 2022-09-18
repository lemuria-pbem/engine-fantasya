<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptNoDeliveryMessage extends AcceptNoPaymentMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough ' . $this->payment . ' to deliver the demand ' . $this->trade . '.';
	}
}
