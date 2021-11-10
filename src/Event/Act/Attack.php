<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;

/**
 * @todo
 */
class Attack implements Act
{
	use ActTrait;
	use MessageTrait;

	public function act(): Attack {
		//TODO
		return $this;
	}
}
