<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command;

trait DefaultActivityTrait
{
	private bool $isDefault = false;

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
	 * Allow execution of other activities of the same class.
	 */
	#[Pure] public function allows(Activity $activity): bool {
		return getClass($activity) === getClass($this);
	}
}
