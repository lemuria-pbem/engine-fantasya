<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class Unmaintained extends AbstractConstructionEffect
{
	public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
