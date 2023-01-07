<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Battle;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Serializable;
use Lemuria\Validate;

class BattleBeginsMessage extends AbstractMessage
{
	private const REGION = 'region';

	private const ATTACKERS = 'attackers';

	private const DEFENDERS = 'defenders';

	protected array $simpleParameters = [self::REGION];

	protected Entity $region;

	/**
	 * @var array<Entity>
	 */
	protected array $attackers = [];

	/**
	 * @var array<Entity>
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

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->region = new Entity();
		$this->region->unserialize($data[self::REGION]);
		foreach ($data[self::ATTACKERS] as $attacker) {
			$entity            = new Entity();
			$this->attackers[] = $entity->unserialize($attacker);
		}
		foreach ($data[self::DEFENDERS] as $defender) {
			$entity            = new Entity();
			$this->defenders[] = $entity->unserialize($defender);
		}
		return $this;
	}

	public function getDebug(): string {
		return 'In region ' . $this->region . ' a battle is raging: ' .
			   'Parties ' . implode(', ', $this->attackers) . ' attack parties ' . implode(', ', $this->defenders) . '.';
	}

	protected function getParameters(): array {
		$region    = $this->region->serialize();
		$attackers = [];
		foreach ($this->attackers as $attacker) {
			$attackers[] = $attacker->serialize();
		}
		$defenders = [];
		foreach ($this->defenders as $defender) {
			$defenders[] = $defender->serialize();
		}
		return [self::REGION => $region, self::ATTACKERS => $attackers, self::DEFENDERS => $defenders];
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

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::REGION, Validate::Array);
		$this->validate($data, self::ATTACKERS, Validate::Array);
		$this->validate($data, self::DEFENDERS, Validate::Array);
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
