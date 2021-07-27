<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Region;
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

		$unit = $this->getBestTacticsUnit();
		if ($unit) {
			$combat->tacticsRound($unit);
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
		foreach ($units as $unit) {
			//TODO: disguised
			$parties->add($unit->Party());
		}
		return $parties;
	}

	protected function getBestTacticsUnit(): ?Unit {
		//TODO
		return null;
	}
}
