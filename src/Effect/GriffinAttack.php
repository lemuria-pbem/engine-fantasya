<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Message\Unit\GriffineggReturnMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GriffineggReturnsMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Griffinegg;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

final class GriffinAttack extends AbstractRegionEffect
{
	use BuilderTrait;

	private ?Unit $griffins = null;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function Griffins(): ?Unit {
		return $this->griffins;
	}

	public function setGriffins(Unit $griffins): GriffinAttack {
		$this->griffins = $griffins;
		return $this;
	}

	protected function run(): void {
		if ($this->griffins) {
			$size       = $this->griffins->Size();
			$region     = $this->griffins->Region();
			$griffin    = self::createCommodity(Griffin::class);
			$resources  = $region->Resources();
			$population = $resources[$griffin]->Count();
			if ($population > 0) {
				$victims = min($population - $size, $population);
				if ($victims > 0) {
					$resources->remove(new Quantity($griffin, $victims));
				}
			}

			$egg  = self::createCommodity(Griffinegg::class);
			$eggs = $resources[$egg]->Count();
			$rest = $this->griffins->Inventory()[$egg]->Count();
			if ($eggs > 0) {
				$loot = min($eggs - $rest, $eggs);
				if ($loot > 0) {
					$resources->remove(new Quantity($egg, $loot));
				}
			}

			if ($size > 0) {
				$this->griffins->setSize(0);
				$this->griffins->Inventory()->clear();
				if ($size === 1) {
					$this->message(GriffineggReturnsMessage::class, $this->griffins)->e($region);
				} else {
					$quantity = new Quantity($griffin, $size);
					$this->message(GriffineggReturnMessage::class, $this->griffins)->e($region)->i($quantity);
				}
			}
		}
		Lemuria::Score()->remove($this);
	}
}
