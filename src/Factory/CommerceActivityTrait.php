<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Activity;

trait CommerceActivityTrait
{
	use DefaultActivityTrait;

	private bool $isCommerceActivity = true;

	/**
	 * Allow execution of commerce activities.
	 */
	public function allows(Activity $activity): bool {
		return isset($this->isCommerceActivity);
	}
}
