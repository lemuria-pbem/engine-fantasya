<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\Unit;

class BattlePlan
{
	/**
	 * @var array<int
	 */
	protected array $battles;

	/**
	 * @var array<string, int>
	 */
	protected array $places = [];

	public static function canAttack(Unit $attacker, Unit $defender): Place {
		$place = new BattlePlace($defender);
		if ($place->Place() === Place::Region) {
			return Place::Region;
		}
		$from   = new BattlePlace($attacker);
		$isSame = $from->__toString() === $place->__toString();
		if ($isSame) {
			return $place->Place();
		}
		return $defender->IsGuarding() ? Place::Region : Place::None;
	}

	public function __construct(array &$battles) {
		$this->battles = &$battles;
	}

	public function canDefend(Unit $unit): bool {
		$battlePlace = new BattlePlace($unit);
		$place       = (string)$battlePlace;
		return isset($this->places[$place]);
	}

	public function getBattleId(Unit $unit): int {
		$battlePlace = new BattlePlace($unit);
		$place       = (string)$battlePlace;
		if (isset($this->places[$place])) {
			return $this->places[$place];
		}

		$id                   = count($this->battles);
		$battle               = new Battle($battlePlace);
		$this->battles[]      = $battle;
		$this->places[$place] = $id;
		return $id;
	}

	public function replaceBattleId(int $old, int $new): void {
		foreach (array_keys($this->places) as $place) {
			if ($this->places[$place] === $old) {
				$this->places[$place] = $new;
			}
		}
	}
}
