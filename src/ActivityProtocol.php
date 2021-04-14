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
	 * @var array(string=>true)
	 */
	private array $activity = [];

	private bool $hasDefault = false;

	private ?Command $defaultCommand = null;

	/**
	 * Create new activity protocol for a unit.
	 */
	public function __construct(private Unit $unit, Context $context) {
		foreach (Lemuria::Orders()->getDefault($unit->Id()) as $order) {
			$command = $context->Factory()->create(new Phrase($order));
			if ($command instanceof Activity) {
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
	#[Pure] public function hasActivity(): bool {
		return !empty($this->activity);
	}

	/**
	 * Check if an activity is allowed.
	 *
	 * Multiple activities of the same kind (e.g. multiple buy or sell commands) are allowed, but execution of a second
	 * activity of a differenz kind than the first activity is forbidden.
	 */
	public function isAllowed(Activity $activity): bool {
		if (empty($this->activity) || isset($this->activity[$activity->Activity()])) {
			return true;
		}
		return false;
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
			if (!$this->isAllowed($command)) {
				return false;
			}
			$this->registerActivity($command);
			if (!$this->hasDefault) {
				$default = $command->getNewDefault();
				if ($default) {
					$this->addDefault($default);
				}
			}
		}
		return true;
	}

	/**
	 * Add a command to the default orders.
	 */
	public function addDefault(UnitCommand $command): void {
		if ($command instanceof Activity) {
			$this->hasDefault = true;
		}
		Lemuria::Orders()->getDefault($this->unit->Id())[] = $command->Phrase();
	}

	protected function registerActivity(Activity $activity): void {
		$this->activity[$activity->Activity()] = true;
	}
}
