<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Command\UnitCommand;

trait OneActivityTrait
{
	use DefaultActivityTrait;

	#[Pure] public function Activity(): string {
		return microtime();
	}

	public function getNewDefault(): ?UnitCommand {
		return $this instanceof UnitCommand ? $this : null;
	}
}
