<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Battle;

class BattleBegins extends AbstractMessage
{
	protected Entity $region;

	/**
	 * @var array(int=>string)
	 */
	protected array $attackers = [];

	/**
	 * @var array(int=>string)
	 */
	protected array $defenders = [];

	public function __construct(Battle $battle) {
		parent::__construct($battle);
		$this->region = new Entity($battle->Region());
		foreach ($battle->Attacker() as $party) {
			$this->attackers[] = new Entity($party);
		}
		foreach ($battle->Defender() as $party) {
			$this->defenders[] = new Entity($party);
		}
	}

	#[Pure] public function __toString(): string {
		return 'In region ' . $this->region . ' a battle is raging: ' .
			   'Parties ' . implode(', ', $this->attackers) . ' attack parties ' . implode(', ', $this->defenders) . '.';
	}
}
