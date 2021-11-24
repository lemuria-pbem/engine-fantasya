<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\People;

/**
 * A monster attacks an enemy unit.
 */
class Attack implements Act
{
	use ActTrait;
	use MessageTrait;

	protected People $enemy;

	public function act(): Attack {
		if (!$this->enemy->isEmpty()) {
			//TODO
			Lemuria::Log()->debug('Monster attacks are not implemented yet.');
		}
		return $this;
	}

	public function setEnemy(People $enemy): Attack {
		$this->enemy = $enemy;
		return $this;
	}
}
