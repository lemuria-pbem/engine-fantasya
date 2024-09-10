<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Talent\Tactics;
use Lemuria\Model\Fantasya\Unit;

class TacticsData implements \Countable
{
	protected final const float ONE_THIRD = 1.0 / 3.0;

	protected const float MIN_TACTICS = 0.05;

	protected const float MIN_ADVANCE = 1.0;

	protected array $sizes = [];

	protected array $tactics = [];

	protected array $isAttacker = [];

	public function count(): int {
		return count($this->sizes);
	}

	/**
	 * @param array<Unit> $units
	 */
	public function add(array $units, bool $isAttacker): static {
		foreach ($units as $unit) {
			$this->addUnit($unit, $isAttacker);
		}
		return $this;
	}

	public function addAttacker(Unit $unit): static {
		return $this->addUnit($unit, true);
	}

	public function addDefender(Unit $unit): static {
		return $this->addUnit($unit, false);
	}

	/**
	 * @return array<Id>
	 */
	public function getBestTacticsCandidates(): array {
		$tactics = [];
		foreach ($this->tactics as $party => $talent) {
			$average         = ($talent / $this->sizes[$party]);
			$tactics[$party] = $average < 1.0 ? $average : $average ** self::ONE_THIRD;
		}
		arsort($tactics, SORT_NUMERIC);

		$attackers = [];
		$attSize   = 0;
		$defenders = [];
		$defSize   = 0;
		foreach ($tactics as $party => $talent) {
			if ($this->isAttacker[$party]) {
				$attackers[] = $party;
				$attSize    += $this->sizes[$party];
			} else {
				$defenders[] = $party;
				$defSize    += $this->sizes[$party];
			}
		}

		$superiority = $attSize / $defSize;
		$bestAtt     = empty($attackers) ? 0.0 : $tactics[$attackers[0]];
		$bestDef     = empty($defenders) ? 0.0 : $tactics[$defenders[0]];
		$advance     = ($bestAtt - $bestDef) * $superiority;
		if ($advance >= self::MIN_ADVANCE && $bestAtt >= self::MIN_TACTICS) {
			return $this->createCandidates($attackers, $tactics);
		}
		if ($advance <= -self::MIN_ADVANCE && $bestDef >= self::MIN_TACTICS) {
			return $this->createCandidates($defenders, $tactics);
		}
		return [];
	}

	public function getTacticsDifference(): TacticsDifference {
		$attacker = 0.0;
		$defender = 0.0;
		foreach ($this->tactics as $party => $talent) {
			$average = ($talent / $this->sizes[$party]);
			if ($this->isAttacker[$party]) {
				$attacker = max($attacker, $average);
			} else {
				$defender = max($defender, $average);
			}
		}
		return new TacticsDifference($attacker, $defender);
	}

	protected function addUnit(Unit $unit, bool $isAttacker): static {
		$party = $unit->Party()->Id()->Id();
		if (!isset($this->sizes[$party])) {
			$this->sizes[$party]      = 0;
			$this->tactics[$party]    = 0;
			$this->isAttacker[$party] = $isAttacker;
		}
		$calculus               = new Calculus($unit);
		$level                  = $calculus->knowledge(Tactics::class)->Level();
		$size                   = $unit->Size();
		$this->sizes[$party]   += $size;
		$this->tactics[$party] += $size * $level ** 3;
		return $this;
	}

	/**
	 * @param array<int> $parties
	 * @param array<int, float>
	 * @return array<Id>
	 */
	private function createCandidates(array $parties, array $tactics): array {
		$first      = $parties[0];
		$best       = $tactics[$first];
		$candidates = [new Id($first)];
		$n          = count($parties);
		for ($i = 1; $i < $n; $i++) {
			$party = $parties[$i];
			if ($tactics[$party] < $best) {
				break;
			}
			$candidates[] = new Id($party);
		}
		return $candidates;
	}
}
