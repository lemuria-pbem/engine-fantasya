<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class QuotaNotSetMessage extends QuotaRemoveMessage
{
	public function create(): string {
		return 'There is no quota set for ' . $this->commodity . ' in region ' . $this->region . '.';
	}
}
