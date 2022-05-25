<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Log\Participant;
use Lemuria\Serializable;

abstract class AbstractBattleSideMessage extends AbstractMessage
{
	/**
	 * @param Participant[]|null $participants
	 */
	public function __construct(protected ?array $participants = []) {
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		foreach ($data['participants'] as $row) {
			$participant          = new Participant();
			$this->participants[] = $participant->unserialize($row);
		}
		return $this;
	}

	protected function getParameters(): array {
		$participants = [];
		foreach ($this->participants as $participant) {
			$participants[] = $participant->serialize();
		}
		return ['participants' => $participants];
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$count   = count($this->participants);
		$stand   = parent::dictionary()->get('combat.stand', $count > 1 ? 1 : 0);
		$message = str_replace('$stand', $stand, $message);
		return str_replace('$participants', $this->participants(), $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'participants', 'array');
	}

	private function participants(): string {
		$participants = [];
		foreach ($this->participants as $participant) {
			$part           = parent::dictionary()->get('combat.participant');
			$part           = str_replace('$unit', (string)$participant->unit, $part);
			$part           = str_replace('$fCount', (string)$participant->fighters, $part);
			$fighter        = parent::dictionary()->get('combat.fighterIn', $participant->fighters > 1 ? 1 : 0);
			$part           = str_replace('$fighterIn', $fighter, $part);
			$part           = str_replace('$cCount', (string)$participant->combatants, $part);
			$combatant      = parent::dictionary()->get('combat.combatant', $participant->combatants > 1 ? 1 : 0);
			$part           = str_replace('$combatant', $combatant, $part);
			$participants[] = $part;
		}
		if (count($participants) > 1) {
			$last = array_pop($participants);
			$and  = parent::dictionary()->get('combat.and');
			return implode(', ', $participants) . ' ' . $and . ' ' . $last;
		}
		return $participants[0];
	}
}
