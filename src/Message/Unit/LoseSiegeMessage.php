<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class LoseSiegeMessage extends LoseEmptyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot throw away anything, the construction is sieged.';
	}
}
