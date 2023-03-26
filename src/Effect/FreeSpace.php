<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;

final class FreeSpace extends AbstractConstructionEffect
{
	private int $space = 0;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
	}

	public function Space(): int {
		return $this->space;
	}

	public function addSpace(int $space): FreeSpace {
		$this->space += $space;
		return $this;
	}

	public function removeSpace(int $space): FreeSpace {
		if ($space > $this->space) {
			throw new LemuriaException('Cannot remove more space than is available.');
		}
		$this->space -= $space;
		return $this;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
