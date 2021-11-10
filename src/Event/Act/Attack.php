<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;

/**
 * A monster attacks an enemy unit.
 */
class Attack implements Act
{
	use ActTrait;
	use MessageTrait;

	protected Unit $enemy;

	public function act(): Attack {
		if ($this->enemy->Size() > 0) {
			//TODO
			Lemuria::Log()->debug('Monster attacks are not implemented yet.');
		}
		return $this;
	}

	public function setEnemy(Unit $unit): Attack {
		$this->enemy = $unit;
		return $this;
	}
}
