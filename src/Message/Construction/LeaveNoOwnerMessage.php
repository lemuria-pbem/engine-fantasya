<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

class LeaveNoOwnerMessage extends AbstractConstructionMessage
{
	protected function create(): string {
		return 'Construction ' . $this->id . ' has been abandoned.';
	}
}
