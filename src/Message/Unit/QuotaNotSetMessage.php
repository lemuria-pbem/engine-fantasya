<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class QuotaNotSetMessage extends QuotaRemoveMessage
{
	protected Result $result = Result::Failure;

	public function create(): string {
		return 'There is no quota set for ' . $this->commodity . ' in region ' . $this->region . '.';
	}
}
