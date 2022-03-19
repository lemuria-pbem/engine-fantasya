<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Activity;

trait OneActivityTrait
{
	use DefaultActivityTrait;

	/**
	 * Do not allow any other activity.
	 */
	#[Pure] public function allows(Activity $activity): bool {
		return false;
	}
}
