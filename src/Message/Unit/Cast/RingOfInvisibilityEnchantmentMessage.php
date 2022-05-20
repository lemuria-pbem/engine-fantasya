<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message;

class RingOfInvisibilityEnchantmentMessage extends AbstractCastMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has an enchanted Gold Ring and can create a Ring of Invisibility unicum now.';
	}
}
