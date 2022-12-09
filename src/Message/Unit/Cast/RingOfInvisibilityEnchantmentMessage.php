<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message\Result;

class RingOfInvisibilityEnchantmentMessage extends AbstractCastMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has an enchanted Gold Ring and can create a Ring of Invisibility unicum now.';
	}
}
