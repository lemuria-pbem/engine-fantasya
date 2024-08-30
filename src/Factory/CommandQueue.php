<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Lemuria;

class CommandQueue
{
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
		if (empty($this->queue[$priority])) {
			return $this;
		}
		if (!($this->queue[$priority][0] instanceof UnitCommand)) {
			return $this;
		}

		$shuffle = $this->priority->getQueueStrategy($priority);
		Lemuria::Log()->debug('Shuffling queue ' . $priority . ' with ' . getClass($shuffle) . ' strategy.');
		$this->queue[$priority] = $shuffle->shuffle($this->queue[$priority]);
		return $this;
	}
}
