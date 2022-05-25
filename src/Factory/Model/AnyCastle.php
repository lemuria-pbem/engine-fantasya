<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Building\Castle;

final class AnyCastle extends AnyBuilding implements Castle
{
	public function Defense(): int {
		return 0;
	}

	public function MaxSize(): int {
		return 0;
	}

	public function MinSize(): int {
		return 0;
	}

	public function Downgrade(): Castle {
		return $this;
	}

	public function Upgrade(): Castle {
		return $this;
	}

	public function Wage(): int {
		return 0;
	}
}
