<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\StringList;

class Rumors extends AbstractUnitEffect
{
	private StringList $rumors;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
		$this->rumors = new StringList();
	}

	public function Rumors(): StringList {
		return $this->rumors;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
