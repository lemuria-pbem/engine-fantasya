<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\LurkMessage;

/**
 * A lurking monster just stays in its region.
 */
class Lurk implements Act
{
	use ActTrait;
	use MessageTrait;

	public function act(): static {
		$region = $this->unit->Region();
		$this->message(LurkMessage::class, $this->unit)->e($region);
		return $this;
	}
}
