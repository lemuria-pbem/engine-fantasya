<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Model\Fantasya\Composition\Carcass as CarcassModel;

final class Carcass extends AbstractOperate
{
	use BurnTrait;

	private const DISINTEGRATE = 6;

	public function take(): void {
		$carcass = $this->getCarcass();

	}

	private function getCarcass(): CarcassModel {
		/** @var CarcassModel $carcass */
		$carcass = $this->operator->Unicum()->Composition();
		return $carcass;
	}
}
