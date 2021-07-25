<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

class Battle
{
	/**
	 * @var Army[]
	 */
	private array $attackers = [];

	/**
	 * @var Army[]
	 */
	private array $defenders = [];

	public function addAttacker(Army $army): Battle {
		$this->attackers[] = $army;
		return $this;
	}

	public function addDefender(Army $army): Battle {
		$this->defenders[] = $army;
		return $this;
	}

	public function commence(): Battle {
		if (empty($this->attackers)) {
			throw new \RuntimeException('No attackers in battle.');
		}
		if (empty($this->defenders)) {
			throw new \RuntimeException('No defenders in battle.');
		}
		//TODO
		return $this;
	}

	public function merge(Battle $battle): Battle {
		$armies = [];
		foreach ($this->attackers as $army) {
			$armies[$army->Id()] = $army;
		}
		foreach ($battle->attackers as $army) {
			$armies[$army->Id()] = $army;
		}
		$this->attackers = array_values($armies);

		$armies = [];
		foreach ($this->defenders as $army) {
			$armies[$army->Id()] = $army;
		}
		foreach ($battle->defenders as $army) {
			$armies[$army->Id()] = $army;
		}
		$this->defenders = array_values($armies);

		return $this;
	}
}
