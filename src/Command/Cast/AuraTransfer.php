<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Model\Fantasya\Factory\BuilderTrait;

final class AuraTransfer extends AbstractCast
{
	use BuilderTrait;

	private const RATE = 2.0;

	public function cast(): void {
		$unit      = $this->cast->Unit();
		$aura      = $this->cast->Aura();
		$available = $unit->Aura()->Aura();
		$demand    = (int)ceil(self::RATE * $aura);
		if ($demand > $available) {
			$transferred = (int)floor($available / self::RATE);
			$reduction   = (int)ceil(self::RATE * $transferred);
		} else {
			$transferred = $aura;
			$reduction   = $demand;
		}
		//TODO
		//$unit->Aura()->consume();
	}
}
