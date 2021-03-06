<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;

/**
 * A protocol of units' activities.
 */
final class ActivityProtocol
{
	/**
	 * @var array(string=>bool)
	 */
	private array $activity = [];

	private ?Activity $defaultCommand = null;

	/**
	 * Create new activity protocol for a unit.
	 */
	public function __construct(private Unit $unit, Context $context) {
		foreach (Lemuria::Orders()->getDefault($unit->Id()) as $order) {
			$command = $context->Factory()->create(new Phrase($order));
			if ($command instanceof Activity) {
				$command->setIsDefault();
				$this->defaultCommand = $command;
				break;
			}
		}
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	/**
	 * Check if unit has an activity already.
	 */
	public function hasActivity(?Activity $command = null): bool {
		return $command ? !$this->isAllowed($command) : !empty($this->activity);
	}

	/**
	 * Get the default command from the previous turn.
	 */
	#[Pure] public function getDefaultCommand(): ?Command {
		return $this->defaultCommand;
	}

	/**
	 * Add a command to the protocol.
	 */
	public function commit(UnitCommand $command): bool {
		Lemuria::Orders()->getCurrent($this->unit->Id())[] = $command->Phrase();
		if ($command instanceof Activity) {
			$default = $command->getNewDefault();
			if ($default && $this->isAllowedAsDefault($default)) {
				$this->addDefaultCommand($default);
			}
			if (!$this->isAllowed($command)) {
				return false;
			}
			$this->activity[$command->Activity()] = true;
		}
		return true;
	}

	/**
	 * Add a command to the default orders.
	 */
	public function addDefault(UnitCommand $command): void {
		$this->addDefaultCommand($command);
		if ($command instanceof Activity) {
			$activity                  = $command->Activity();
			$value                     = isset($this->activity[$activity]) && $this->activity[$activity];
			$this->activity[$activity] = $value;
		}
	}

	/**
	 * Replace default orders that have changed after command execution.
	 */
	public function replaceDefault(UnitCommand $search, ?UnitCommand $replace = null): void {
		$instructions = Lemuria::Orders()->getDefault($this->unit->Id());
		$phrase       = (string)$search->Phrase();
		foreach ($instructions as $i => $default) {
			if ($default === $phrase) {
				if ($replace) {
					$instructions[$i] = (string)$replace->Phrase();
				} else {
					unset($instructions[$i]);
				}
				break;
			}
		}
	}

	/**
	 * Check if an activity is allowed.
	 *
	 * Multiple activities of the same kind (e.g. multiple buy or sell commands) are allowed, but execution of a second
	 * activity of a different kind than the first activity is forbidden.
	 */
	private function isAllowed(Activity $activity): bool {
		if (empty($this->activity)) {
			return true;
		}
		$key = $activity->Activity();
		if (array_key_exists($key, $this->activity)) {
			return true;
		}
		return array_sum($this->activity) === 0;
	}

	private function isAllowedAsDefault(Activity $activity): bool {
		if (empty($this->activity)) {
			return true;
		}
		$key = $activity->Activity();
		return isset($this->activity[$key]);
	}

	private function addDefaultCommand(UnitCommand $command): void {
		$defaults   = Lemuria::Orders()->getDefault($this->unit->Id());
		$defaults[] = $command;
	}
}
