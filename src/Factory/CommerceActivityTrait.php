<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Activity;

trait CommerceActivityTrait
{
	use DefaultActivityTrait;

	private bool $isCommerceActivity = true;

	/**
	 * Allow execution of commerce activities.
	 */
	#[Pure] public function allows(Activity $activity): bool {
		return isset($this->isCommerceActivity);
	}
}
