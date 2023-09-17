<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Statistics;

use Lemuria\Model\Fantasya\Continent;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Population;

final class ContinentPopulation extends Population
{
	public function __construct(private Continent $continent) {
		foreach ($continent->Landmass() as $region) {
			foreach ($region->Residents() as $unit) {
				$this->add(new Gang($unit->Race(), $unit->Size()));
			}
		}
	}

	public function Continent(): Continent {
		return $this->continent;
	}
}
