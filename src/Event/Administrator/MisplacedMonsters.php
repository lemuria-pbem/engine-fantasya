<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity\Monster\Kraken;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Lake;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

/**
 * This event converts the landscape of lakes to the new Lake.
 */
final class MisplacedMonsters extends AbstractEvent
{
	use BuilderTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function run(): void {
		$lake = self::createLandscape(Lake::class);
		$kraken = self::createMonster(Kraken::class);
		foreach (Lemuria::Catalog()->getAll(Domain::Location) as $region /* @var Region $region */) {
			if ($region->Landscape() === $lake) {
				$krakens   = [];
				$residents = $region->Residents();
				foreach ($residents as $unit /* @var Unit $unit */) {
					if ($unit->Race() === $kraken) {
						$krakens[] = $unit;
					}
				}
				foreach ($krakens as $unit) {
					$residents->remove($unit);
					$unit->Party()->People()->remove($unit);
					Lemuria::Catalog()->remove($unit);
					Lemuria::Log()->debug('Kraken ' . $unit->Id() . ' has been removed from lake ' . $region->Id() . '.');
				}
			}
		}
	}
}
