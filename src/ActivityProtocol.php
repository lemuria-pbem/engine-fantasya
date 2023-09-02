<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Command\Comment;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Factory\DefaultResolver;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;

/**
 * A protocol of units' activities.
 */
final class ActivityProtocol
{
	/**
	 * @var array<Activity>
	 */
	private array $activities = [];

	/**
	 * @var array<Command>
	 */
	private array $defaults = [];

	/**
	 * @var array<Activity>
	 */
	private array $defaultActivities = [];

	/**
	 * @var array<Activity>
	 */
	private array $alternativeActivities = [];

	/**
	 * @var array<Activity>
	 */
	private array $plannedActivities = [];

	/**
	 * @var array<Comment>
	 */
	private array $comments = [];

	/**
	 * Create new activity protocol for a unit.
	 */
	public function __construct(private readonly Unit $unit) {
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	/**
	 * Check if unit has an activity already.
	 */
	public function hasActivity(?Activity $command = null): bool {
		return $command ? !$this->isAllowed($command) : !empty($this->activities);
	}

	/**
	 * Check if unit has executed an alternative activity.
	 */
	public function hasAlternativeActivity(): bool {
		foreach ($this->activities as $activity) {
			if ($activity->IsAlternative()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get all planned activities.
	 */
	public function getPlannedActivities(): array {
		return $this->plannedActivities;
	}

	/**
	 * Add a planned activity.
	 */
	public function addPlannedActivity(Activity $activity): ActivityProtocol {
		$this->plannedActivities[] = $activity;
		return $this;
	}

	/**
	 * Add a command to the current protocol.
	 */
	public function logCurrent(UnitCommand $command): void {
		Lemuria::Orders()->getCurrent($this->unit->Id())[] = $command->getInstruction();
	}

	/**
	 * Add a command to the protocol.
	 */
	public function commit(UnitCommand $command): bool {
		$this->logCurrent($command);
		if ($command instanceof Activity) {
			if ($this->isAllowed($command)) {
				$this->activities[] = $command;
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * Add a command to the default orders.
	 */
	public function addDefault(UnitCommand $command): void {
		if ($command instanceof Comment) {
			$this->comments[] = $command;
		} elseif ($command instanceof Activity) {
			$this->defaultActivities[] = $command;
		} else {
			$this->defaults[] = $command;
		}
	}

	/**
	 * Add the new default of an Activity.
	 */
	public function addNewDefaults(Activity $activity): void {
		foreach ($activity->getNewDefaults() as $default) {
			if ($default instanceof Activity) {
				if ($default->IsAlternative()) {
					$this->alternativeActivities[] = $default;
				} else {
					$this->defaultActivities[] = $default;
				}
			}
		}
	}

	/**
	 * Add the new default of an Activity.
	 */
	public function replaceDefaults(Activity $activity): void {
		$defaults = $activity->getNewDefaults();
		if (empty($defaults)) {
			/** @var UnitCommand $activity */
			$default = $activity->getInstruction();
			foreach ($this->defaultActivities as $activity) {
				if ($activity->getInstruction() !== $default) {
					$defaults[] = $activity;
				}
			}
		}
		$this->defaultActivities = $defaults;
	}

	public function reassignDefaultActivity(string $old, Activity $new): void {
		$n = count($this->defaultActivities);
		for ($i = 0; $i < $n; $i++) {
			$activity = $this->defaultActivities[$i];
			if ($activity->getInstruction() === $old) {
				$this->defaultActivities[$i] = $new;
			}
		}
	}

	/**
	 * Determine which new defaults are persisted.
	 */
	public function persistNewDefaults(): void {
		$defaults = Lemuria::Orders()->getDefault($this->unit->Id());
		$resolves = [];
		array_push($resolves, ...$this->defaults, ...$this->defaultActivities, ...$this->alternativeActivities, ...$this->comments);
		$resolver = new DefaultResolver($this, $resolves);
		foreach ($resolver->resolve() as $command) {
			$defaults[] = $command->getInstruction();
		}
	}

	/**
	 * Check if an activity is allowed.
	 */
	public function isAllowed(Activity $activity): bool {
		foreach ($this->activities as $oldActivity) {
			if (!$oldActivity->allows($activity)) {
				return false;
			}
		}
		return true;
	}
}
