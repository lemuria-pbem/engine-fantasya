<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Lemuria;

trait DefaultActivityTrait
{
	private bool $isDefault = false;

	private bool $preventDefault = false;

	/**
	 * Get the activity class.
	 */
	#[Pure] public function Activity(): string {
		return getClass($this);
	}

	/**
	 * Check if this activity is the unit's default activity.
	 */
	public function IsDefault(): bool {
		return $this->isDefault;
	}

	/**
	 * Get the new default commands.
	 *
	 * @return Command[]
	 */
	public function getNewDefaults(): array {
		return $this->preventDefault ? [] : [$this];
	}

	/**
	 * Set the default status.
	 */
	public function setIsDefault(bool $isDefault = true): void {
		$this->isDefault = $isDefault;
	}

	/**
	 * Prevent that this command is used as new default.
	 */
	public function preventDefault(): Activity {
		$this->preventDefault = true;
		return $this;
	}

	/**
	 * Check if this activity allows execution of another activity.
	 */
	public function allows(Activity $activity): bool {
		return true;
	}

	/**
	 * Replace default orders that have changed after command execution.
	 */
	protected function replaceDefault(UnitCommand $search, ?UnitCommand $replace = null): void {
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
}
