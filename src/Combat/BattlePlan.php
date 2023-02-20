<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\Building\Canal;
use Lemuria\Model\Fantasya\Building\Monument;
use Lemuria\Model\Fantasya\Building\Quay;
use Lemuria\Model\Fantasya\Building\Ruin;
use Lemuria\Model\Fantasya\Building\Signpost;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Unit;

class BattlePlan
{
	protected const ATTACK_REGION = [Canal::class, Monument::class, Quay::class, Ruin::class, Signpost::class, Site::class];

	/**
	 * @var array<int
	 */
	protected array $battles;

	/**
	 * @var array<string, int>
	 */
	protected array $places = [];

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

	public static function canAttack(Unit $attacker, Unit $defender): Place {
		$place = new BattlePlace($defender);
		if ($place->Place() === Place::Region) {
			return Place::Region;
		}
		$from = new BattlePlace($attacker);
		return $from->__toString() === $place->__toString() ? $place->Place() : Place::None;
	}
}
