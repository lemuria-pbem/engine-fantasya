<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
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

	#[ArrayShape(['participants' => 'array'])]
	#[Pure] protected function getParameters(): array {
		$participants = [];
		foreach ($this->participants as $participant) {
			$participants[] = $participant->serialize();
		}
		return ['participants' => $participants];
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'participants', 'array');
	}
}
