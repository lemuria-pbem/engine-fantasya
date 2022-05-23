<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Battle;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;

class BattleBeginsMessage extends AbstractMessage
{
	protected array $simpleParameters = ['region'];

	protected Entity $region;

	/**
	 * @var array(int=>string)
	 */
	protected array $attackers = [];

	/**
	 * @var array(int=>string)
	 */
	protected array $defenders = [];

	public function __construct(Battle $battle = null) {
		if ($battle) {
			$this->region = new Entity($battle->Region());
			foreach ($battle->Attacker() as $party) {
				$this->attackers[] = new Entity($party);
			}
			foreach ($battle->Defender() as $party) {
				$this->defenders[] = new Entity($party);
			}
		}
	}

	public function getDebug(): string {
		return 'In region ' . $this->region . ' a battle is raging: ' .
			   'Parties ' . implode(', ', $this->attackers) . ' attack parties ' . implode(', ', $this->defenders) . '.';
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$aParty  = parent::dictionary()->get('combat.party', count($this->attackers) > 1 ? 1 : 0);
		$message = str_replace('$aParty', $aParty, $message);
		$message = str_replace('$attacker', $this->entities($this->attackers), $message);
		$attack  = parent::dictionary()->get('combat.attack', count($this->attackers) > 1 ? 1 : 0);
		$dParty  = parent::dictionary()->get('combat.party', count($this->defenders) > 1 ? 1 : 0);
		$message = str_replace('$dParty', $dParty, $message);
		$message = str_replace('$attack', $attack, $message);
		return str_replace('$defender', $this->entities($this->defenders), $message);
	}

	private function entities(array $side): string {
		$entities = [];
		foreach ($side as $entity) {
			$entities[] = (string)$entity;
		}
		if (count($entities) > 1) {
			$last = array_pop($entities);
			$and  = parent::dictionary()->get('combat.and');
			return implode(', ', $entities) . ' ' . $and . ' ' . $last;
		}
		return $entities[0];
	}
}
