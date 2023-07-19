<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Animal;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Herb;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Highland;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Landscape\Swamp;
use Lemuria\Model\Fantasya\Quota;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Regulation;

class RealmQuota
{
	public final const DEFAULT_TREE = [Forest::class => 600, Highland::class => 100, Plain::class => 100, Swamp::class => 40];

	public final const DEFAULT_ANIMAL = [Horse::class => 100, Camel::class => 100, Elephant::class => 50];

	public final const DEFAULT_HERB = 0.5;

	protected readonly Regulation $regulation;

	public function __construct(Realm $realm) {
		$this->regulation = $realm->Party()->Regulation();
	}

	public function getQuota(Region $region, Commodity $commodity): Quota {
		$quota = $this->regulation->getQuotas($region)?->getQuota($commodity);
		if (!$quota) {
			$quota = new Quota($commodity, $this->getThreshold($region, $commodity));
		}
		return $quota;
	}

	protected function getThreshold(Region $region, Commodity $commodity): float|int {
		$threshold = 0;
		if ($commodity instanceof Wood) {
			$landscape = $region->Landscape()::class;
			if (isset(self::DEFAULT_TREE[$landscape])) {
				$threshold = self::DEFAULT_TREE[$landscape];
			}
		} elseif ($commodity instanceof Animal) {
			$animal = $commodity::class;
			if (isset(self::DEFAULT_ANIMAL[$animal])) {
				$threshold = self::DEFAULT_ANIMAL[$animal];
			}
		} elseif ($commodity instanceof Herb) {
			$threshold = self::DEFAULT_HERB;
		}
		return $threshold;
	}
}
