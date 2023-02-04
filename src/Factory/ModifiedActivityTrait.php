<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\UnitCommand;

trait ModifiedActivityTrait
{
	use DefaultActivityTrait;

	private ?UnitCommand $newDefault = null;

	/**
	 * @return array<Command>
	 */
	public function getNewDefaults(): array {
		if ($this->preventDefault) {
			return [];
		}
		return $this->newDefault ? [$this->newDefault] : [];
	}
}
