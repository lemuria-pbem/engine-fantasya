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
	 * @var array<string, int>
	 */
	protected array $battles = [];

	public function __construct(?int $battle = null) {
		if (is_int($battle)) {
			$this->battles[Place::Region->name] = $battle;
		}
	}

	public function getBattle(Place $place): int {
		return $this->battles[$place->name];
	}

	public static function canAttack(Unit $attacker, Unit $defender): Place {
		$place = $defender->Construction();
		if ($place) {
			$from = $attacker->Construction();
			if (!in_array($place->Building()::class, self::ATTACK_REGION)) {
				if ($place === $from) {
					return Place::Building;
				}
				return Place::None;
			}
		} else {
			$vessel = $defender->Vessel();
			if ($vessel) {
				return $vessel === $attacker->Vessel() ? Place::Ship : Place::None;
			}
		}
		return Place::Region;
	}
}
