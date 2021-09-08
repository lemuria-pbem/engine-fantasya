<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

class BattleBegins extends AbstractMessage
{
	protected string $region;

	protected array $attackers = [];

	protected array $defenders = [];

	protected function create(): string {
		$attacker = count($this->attackers) > 1 ? 'parties ' : 'party ';
		$attacks  = count($this->attackers) > 1 ? ' attack ' : ' attacks ';
		$defender = count($this->defenders) > 1 ? 'parties ' : 'party ';
		return 'In region ' . $this->region . ' a battle is raging: The ' . $attacker . $this->parties($this->attackers) . $attacks . $defender . $this->parties($this->defenders) . '.';
	}

	private function parties(array $parties): string {
		$n = count($parties);
		if ($n > 2) {
			$firstParties = array_slice($parties, 0, $n - 1);
			return implode(', ', $firstParties) . ' and ' . $parties[$n - 1];
		} elseif ($n > 1) {
			return $parties[0] . ' and ' . $parties[1];
		} else {
			return (string)$parties[0];
		}
	}
}
