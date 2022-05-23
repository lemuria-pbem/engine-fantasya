<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Activity;

trait OperatorActivityTrait
{
	use DefaultActivityTrait;

	private bool $isOperatorActivity = true;

	/**
	 * Allow execution of unicum creation and writing activities.
	 */
	public function allows(Activity $activity): bool {
		return isset($this->isOperatorActivity);
	}
}
