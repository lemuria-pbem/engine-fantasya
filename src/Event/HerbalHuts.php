<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\HerbalHut;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\HerbalBook;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Talent\Herballore;

/**
 * Conduct herbage exploration of units in herbage huts.
 */
final class HerbalHuts extends AbstractEvent
{
	use BuilderTrait;
	use SiegeTrait;

	private Building $hut;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->hut = self::createBuilding(HerbalHut::class);
	}

	protected function run(): void {
		foreach (Construction::all() as $construction) {
			if ($construction->Building() === $this->hut) {
				$size = 0;
				$inhabitants = $construction->Inhabitants();
				foreach ($inhabitants as $unit) {
					$calculus = $this->context->getCalculus($unit);
					if ($this->canEnterOrLeave($unit)) {
						if ($calculus->knowledge(Herballore::class)->Level() >= Herballore::EXPLORE_LEVEL) {
							$size += $unit->Size();
						}
					}
				}
				if ($size > 0) {
					$herbalBook = $inhabitants->Owner()->Party()->HerbalBook();
					$central    = $construction->Region();
					$this->explore($central, $herbalBook);
					$size--;

					$territory = $central->Realm()?->Territory();
					if ($central === $territory?->Central() && $size > 0) {
						$regions = [];
						foreach ($territory as $region) {
							if ($region === $central) {
								continue;
							}
							$regions[] = $region;
						}

						$n      = count($regions);
						$excess = $n - $size;
						for ($i = 0; $i < $excess; $i++) {
							$index = randInt(max: --$n);
							unset($regions[$index]);
							$regions = array_values($regions);
						}

						foreach ($regions as $region) {
							$this->explore($region, $herbalBook);
						}
					}
				}
			}
		}
	}

	private function explore(Region $region, HerbalBook $herbalBook): void {
		$herbage = $region->Herbage();
		$herbalBook->record($region, $herbage);
		Lemuria::Log()->debug('Herbage in ' . $region . ' is ' . $herbage->Occurrence() . ' ' . $herbage->Herb() . '.');
	}
}
