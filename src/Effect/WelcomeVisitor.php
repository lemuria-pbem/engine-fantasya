<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\Scenario\Visitation;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class WelcomeVisitor extends AbstractUnitEffect
{
	private ?Visitation $visitation = null;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function Visitation(): Visitation {
		return $this->visitation;
	}

	public function setVisitation(Visitation $visitation): WelcomeVisitor {
		$this->visitation = $visitation;
		return $this;
	}

	protected function run(): void {
		if (!$this->visitation) {
			Lemuria::Score()->remove($this);
		}
	}
}
