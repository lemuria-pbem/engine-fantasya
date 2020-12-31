<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Lemuria\People;
use Lemuria\Model\Lemuria\Region;
use Lemuria\Model\Lemuria\Unit;

/**
 * Helper class for region information.
 */
final class Intelligence
{
	#[Pure] public function __construct(private Region $region) {
	}

	/**
	 * Get the guards of the region.
	 */
	public function getGuards(): People {
		$guards = new People();
		foreach ($this->region->Residents() as $unit /* @var Unit $unit */) {
			if ($unit->IsGuarding()) {
				$guards->add($unit);
			}
		}
		return $guards;
	}
}
