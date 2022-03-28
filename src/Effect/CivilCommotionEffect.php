<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class CivilCommotionEffect extends AbstractRegionEffect
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
