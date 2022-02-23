<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

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
	 * @var Activity[]
	 */
	private array $activities = [];

	/**
	 * @var Command[]
	 */
	private array $defaults = [];

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
	 *
	 * - Layabout
	 * - commitCommand() in Teach / Travel
	 */
	public function hasActivity(?Activity $command = null): bool {
		return $command ? !$this->isAllowed($command) : !empty($this->activities);
	}

	/**
	 * Add a command to the protocol.
	 *
	 * - UnitTrait / commitCommand()
	 */
	public function commit(UnitCommand $command): bool {
		if ($command instanceof Activity) {
			if ($this->isAllowed($command)) {
				Lemuria::Orders()->getCurrent($this->unit->Id())[] = $command->Phrase();
				$this->activities[]                                = $command;
				return true;
			}
			return false;
		}

		Lemuria::Orders()->getCurrent($this->unit->Id())[] = $command->Phrase();
		return true;
	}

	/**
	 * Add a command to the default orders.
	 *
	 * - Comment
	 * - Copy
	 * - DefaultCommand
	 * - Travel
	 */
	public function addDefault(UnitCommand $command): void {
		$this->defaults[] = $command;
	}

	/**
	 * Add the new default of an Activity.
	 */
	public function addNewDefaults(Activity $activity): void {
		foreach ($activity->getNewDefaults() as $default) {
			$this->defaults[] = $default;
		}
	}

	/**
	 * Determine which new defaults are persisted.
	 */
	public function persistNewDefaults(): void {
		$defaults = Lemuria::Orders()->getDefault($this->unit->Id());
		$resolver = new DefaultResolver($this->defaults);
		foreach ($resolver->resolve() as $command) {
			$defaults[] = $command;
		}
	}

	/**
	 * Check if an activity is allowed.
	 *
	 * Multiple activities of the same kind (e.g. multiple buy or sell commands) are allowed, but execution of a second
	 * activity of a different kind than the first activity is forbidden.
	 */
	private function isAllowed(Activity $activity): bool {
		foreach ($this->activities as $oldActivity) {
			if (!$oldActivity->allows($activity)) {
				return false;
			}
		}
		return true;
	}
}
