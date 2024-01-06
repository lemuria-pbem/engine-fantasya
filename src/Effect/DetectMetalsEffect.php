<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Landmass;

final class DetectMetalsEffect extends AbstractPartyEffect
{
	protected ?bool $isReassign = null;

	private Landmass $regions;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->regions = new Landmass();
	}

	public function Regions(): Landmass {
		return $this->regions;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
