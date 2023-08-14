<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Turn;

use Lemuria\Id;
use Lemuria\Model\Fantasya\Party;

class SelectiveCherryPicker implements CherryPicker
{
	private bool $partyDefault = false;

	private bool $priorityDefault = true;

	/**
	 * @var array<int, bool>
	 */
	private array $parties = [];

	/**
	 * @var array<int, bool>
	 */
	private array $priorities = [];

	public function pickParty(Id|Party|int|string $party): bool {
		$id = $this->determineId($party);
		if (array_key_exists($id, $this->parties)) {
			return $this->parties[$id];
		}
		return $this->partyDefault;
	}

	public function pickPriority(int $priority): bool {
		if (array_key_exists($priority, $this->priorities)) {
			return $this->priorities[$priority];
		}
		return $this->priorityDefault;
	}

	public function run(int $priority): static {
		$this->priorities[$priority] = true;
		return $this;
	}

	public function skip(int $priority): static {
		$this->priorities[$priority] = false;
		return $this;
	}

	public function everyone(): static {
		$this->partyDefault = true;
		return $this;
	}

	public function none(): static {
		$this->partyDefault = false;
		return $this;
	}

	public function add(Id|Party|int|string $party): static {
		$id                 = $this->determineId($party);
		$this->parties[$id] = true;
		return $this;
	}

	public function remove(Id|Party|int|string $party): static {
		$id                 = $this->determineId($party);
		$this->parties[$id] = false;
		return $this;
	}

	public function everything(): static {
		$this->priorityDefault = true;
		return $this;
	}

	public function nothing(): static {
		$this->priorityDefault = false;
		return $this;
	}

	protected function determineId(Id|Party|int|string $party): int {
		if ($party instanceof Id) {
			return $party->Id();
		}
		if ($party instanceof Party) {
			return $party->Id()->Id();
		}
		if (is_string($party)) {
			if (preg_match('/^' . Id::REGEX . '$/', $party) === 1) {
				return Id::fromId($party)->Id();
			}
			return $this->idFromUuid($party);
		}
		return $party;
	}

	private function idFromUuid(string $uuid): int {
		foreach (Party::all() as $party) {
			if ($party->Uuid() === $uuid) {
				return $party->Id()->Id();
			}
		}
		return 0;
	}
}
