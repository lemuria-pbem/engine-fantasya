<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Statistics;

use Lemuria\Model\Fantasya\Continent;
use Lemuria\Model\Fantasya\Scene;
use Lemuria\Model\Fantasya\Scenery;

final class ContinentScenery extends Scenery
{
	public function __construct(private Continent $continent) {
		foreach ($continent->Landmass() as $region) {
			$this->add(new Scene($region->Landscape()));
		}
	}

	public function Continent(): Continent {
		return $this->continent;
	}
}
