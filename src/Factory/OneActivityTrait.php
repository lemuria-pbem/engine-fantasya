<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Activity;

trait OneActivityTrait
{
	use DefaultActivityTrait;

	/**
	 * Do not allow any other activity.
	 */
	public function allows(Activity $activity): bool {
		return false;
	}
}
