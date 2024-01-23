<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class OfferUnicumMessage extends OfferMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' creates a new offer for unicum ' . $this->trade . '.';
	}
}
