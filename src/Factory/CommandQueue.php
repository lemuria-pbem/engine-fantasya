<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Lemuria;

class CommandQueue
{
	use RealmTrait;

	/**
	 * @var array<int, array>
	 */
	protected array $queue = [];

	private CommandPriority $priority;

	public function __construct() {
		$this->priority = CommandPriority::getInstance();
		foreach (CommandPriority::ORDER as $priority) {
			$this->queue[$priority] = [];
		}
	}

	/**
	 * @return array<int>
	 */
	public function getPriorities(): array {
		return array_keys($this->queue);
	}

	/**
	 * @return array<Action>
	 */
	public function getActions(int $priority): array {
		return $this->queue[$priority] ?? [];
	}

	public function count(int $priority): int {
		return count($this->queue[$priority] ?? []);
	}

	public function remove(int $priority): static {
		unset($this->queue[$priority]);
		return $this;
	}

	public function add(Action $action): static {
		$priority                 = $this->priority->getPriority($action);
		$this->queue[$priority][] = $action;
		return $this;
	}

	public function shuffle(int $priority): static {
		if (!$this->priority->canShuffle($priority)) {
			return $this;
		}
		if (empty($this->queue[$priority])) {
			return $this;
		}
		if (!($this->queue[$priority][0] instanceof UnitCommand)) {
			return $this;
		}

		Lemuria::Log()->debug('Shuffling queue ' . $priority . '.');
		$queue = [];
		$units = [];
		foreach ($this->queue[$priority] as $action) {
			if ($this->isRealmCommand($action)) {
				$queue[] = $action;
				continue;
			}

			$id = $action->Unit()->Id()->Id();
			if (!isset($units[$id])) {
				$units[$id] = [];
			}
			$units[$id][] = $action;
		}

		shuffle($units);
		foreach ($units as $actions) {
			array_push($queue, ...$actions);
		}
		$this->queue[$priority] = $queue;
		return $this;
	}
}
