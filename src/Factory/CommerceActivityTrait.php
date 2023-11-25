<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\CommerceCommand;

trait CommerceActivityTrait
{
	use DefaultActivityTrait;

	/**
	 * Allow execution of commerce activities.
	 */
	public function allows(Activity $activity): bool {
		return $activity instanceof CommerceCommand;
	}
}
