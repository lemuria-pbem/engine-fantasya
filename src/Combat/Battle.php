<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Factory\Model\DisguisedParty;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Talent\Tactics;
use Lemuria\Model\Fantasya\Unit;

class Battle
{
	/**
	 * @var Unit[]
	 */
	private array $attackers = [];

	/**
	 * @var Unit[]
	 */
	private array $defenders = [];

	#[Pure] public function __construct(private Region $region) {
	}

	public function Region(): Region {
		return $this->region;
	}

	public function Attacker(): Gathering {
		return $this->getParties($this->attackers);
	}

	public function Defender(): Gathering {
		return $this->getParties($this->defenders);
	}

	public function addAttacker(Unit $unit): Battle {
		$this->attackers[] = $unit;
		return $this;
	}

	public function addDefender(Unit $unit): Battle {
		$this->defenders[] = $unit;
		return $this;
	}

	public function commence(): Battle {
		if (empty($this->attackers)) {
			throw new \RuntimeException('No attackers in battle.');
		}
		if (empty($this->defenders)) {
			throw new \RuntimeException('No defenders in battle.');
		}

		$combat = new Combat();
		foreach ($this->attackers as $unit) {
			$combat->addAttacker($unit);
		}
		foreach ($this->defenders as $unit) {
			$combat->addDefender($unit);
		}

		$party = $this->getBestTacticsParty();
		if ($party) {
			$combat->tacticsRound($party);
		} else {
			Lemuria::Log()->debug('Both sides are tactically equal.');
		}
		while ($combat->hasAttackers() && $combat->hasDefenders()) {
			$combat->nextRound();
		}

		return $this;
	}

	public function merge(Battle $battle): Battle {
		$armies = [];
		foreach ($this->attackers as $unit) {
			$armies[$unit->Id()->Id()] = $unit;
		}
		foreach ($battle->attackers as $unit) {
			$armies[$unit->Id()->Id()] = $unit;
		}
		$this->attackers = array_values($armies);

		$armies = [];
		foreach ($this->defenders as $unit) {
			$armies[$unit->Id()->Id()] = $unit;
		}
		foreach ($battle->defenders as $unit) {
			$armies[$unit->Id()->Id()] = $unit;
		}
		$this->defenders = array_values($armies);

		return $this;
	}

	protected function getParties(array $units): Gathering {
		$parties = new Gathering();
		foreach ($units as $unit /* @var Unit $unit */) {
			$disguise = $unit->Disguise();
			if ($disguise) {
				$parties->add($disguise);
			} elseif ($disguise === null) {
				$parties->add(new DisguisedParty());
			} else {
				$parties->add($unit->Party());
			}
		}
		return $parties;
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	protected function getBestTacticsParty(): ?Party {
		$tactics    = [];
		$isAttacker = [];
		foreach ($this->attackers as $unit) {
			$party = $unit->Party()->Id()->Id();
			if (!isset($tactics[$party])) {
				$tactics[$party]    = 0;
				$isAttacker[$party] = true;
			}
			$calculus         = new Calculus($unit);
			$level            = $calculus->knowledge(Tactics::class)->Level();
			$tactics[$party] += $unit->Size() * $level ** 3;
		}
		foreach ($this->defenders as $unit) {
			$party = $unit->Party()->Id()->Id();
			if (!isset($tactics[$party])) {
				$tactics[$party]    = 0;
				$isAttacker[$party] = false;
			}
			$calculus         = new Calculus($unit);
			$level            = $calculus->knowledge(Tactics::class)->Level();
			$tactics[$party] += $unit->Size() * $level ** 3;
		}

		arsort($tactics);
		$candidates = [0];
		$n          = count($tactics);
		$party      = array_keys($tactics);
		$talent     = array_values($tactics);
		for ($i = 1; $i < $n; $i++) {
			if ($talent[$i] < $talent[0]) {
				break;
			}
			$candidates[] = $i;
		}
		foreach ($candidates as $i) {
			if ($isAttacker[$party[$i]] !== $isAttacker[$party[0]]) {
				return null;
			}
		}
		$chosen = $candidates[array_rand($candidates)];
		return Party::get(new Id($party[$chosen]));
	}
}
