<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Building\Castle;

final class AnyCastle extends AnyBuilding implements Castle
{
	#[Pure] public function Defense(): int {
		return 0;
	}

	#[Pure] public function MaxSize(): int {
		return 0;
	}

	#[Pure] public function MinSize(): int {
		return 0;
	}

	public function Downgrade(): Castle {
		return $this;
	}

	public function Upgrade(): Castle {
		return $this;
	}

	#[Pure] public function Wage(): int {
		return 0;
	}
}
