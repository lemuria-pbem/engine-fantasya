<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Activity;

trait OperatorActivityTrait
{
	use DefaultActivityTrait;

	private bool $isOperatorActivity = true;

	/**
	 * Allow execution of unicum creation and writing activities.
	 */
	#[Pure] public function allows(Activity $activity): bool {
		return isset($this->isOperatorActivity);
	}
}
