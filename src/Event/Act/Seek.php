<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\SeekMessage;
use Lemuria\Model\Fantasya\Commodity\Monster\Bear;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Wolf;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\People;

/**
 * A seeking monster tries to spot a random outdoor player unit in the region.
 */
class Seek implements Act
{
	use ActTrait;
	use MessageTrait;

	protected const MONSTER = [
		Zombie::class => [Bear::class, Goblin::class, Wolf::class]
	];

	protected People $enemy;

	public function __construct(Behaviour $behaviour) {
		$this->unit  = $behaviour->Unit();
		$this->enemy = new People();
	}

	public function Enemy(): People {
		return $this->enemy;
	}

	public function act(): static {
		$calculus = new Calculus($this->unit);
		$races    = self::MONSTER[$this->unit->Race()::class] ?? [];
		$region   = $this->unit->Region();
		foreach ($region->Residents() as $unit) {
			if ($unit->Party()->Type() === Type::Monster) {
				if (in_array($unit->Race()::class, $races) && $unit->Size() > 0) {
					$this->enemy->add($unit);
				}
				continue;
			}
			if ($unit->Construction() || $unit->Vessel()) {
				continue;
			}
			if ($unit->Size() > 0 && $calculus->canDiscover($unit)) {
				$this->enemy->add($unit);
			}
		}
		if ($this->enemy->count() > 0) {
			$this->message(SeekMessage::class, $this->unit)->e($region);
		}
		return $this;
	}
}
