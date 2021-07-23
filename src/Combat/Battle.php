<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

class Battle
{
	/**
	 * @var array(int=>Army)
	 */
	private array $armies = [];

	/**
	 * @var array(int=>true)
	 */
	private array $attackers = [];

	/**
	 * @var array(int=>true)
	 */
	private array $defenders = [];

	#[Pure] public function __construct(private Region $region) {
	}

	public function getArmy(Unit $unit): Army {
		$army = $this->findArmy($unit);
		if (!$army) {
			$army = new Army($unit->Party());
			$this->addArmy($army->add($unit));
		}
		return $army;
	}

	public function addAttack(Army $attacker, Army $defender): Battle {
		$this->attackers[$this->addArmy($attacker)] = true;
		$this->defenders[$this->addArmy($defender)] = true;
		return $this;
	}

	public function commence(): Battle {
		//TODO
		return $this;
	}

	protected function findArmy(Unit $unit): ?Army {
		foreach ($this->armies as $army) {
			if ($army->Units()->has($unit->Id())) {
				return $army;
			}
		}
		return null;
	}

	protected function addArmy(Army $army): int {
		$id = $army->Id();
		if (!isset($this->armies[$id])) {
			$this->armies[$id] = $army;
		}
		return $id;
	}
}
