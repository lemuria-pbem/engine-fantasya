<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class QuotaNoHerbMessage extends QuotaRemoveHerbMessage
{
	protected Result $result = Result::Failure;

	public function create(): string {
		return 'There is no quota set for herbs in region ' . $this->region . '.';
	}
}
