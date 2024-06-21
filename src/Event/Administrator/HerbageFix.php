<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Factory\HerbGenerator;
use Lemuria\Model\Fantasya\Herbage;
use Lemuria\Model\Fantasya\HerbalBook;
use Lemuria\Model\Fantasya\Landscape;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Region;

/**
 * This event is a one-time fix for regions that grow the wrong herb.
 */
final class HerbageFix extends AbstractEvent
{
	use BuilderTrait;

	private HerbGenerator $generator;

	private Landscape $plain;

	private Landscape $forest;

	private array $pfHerbs;

	private HerbalBook $herbalBook;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->generator  = new HerbGenerator();
		$this->plain      = self::createLandscape(Plain::class);
		$this->forest     = self::createLandscape(Forest::class);
		$this->pfHerbs    = array_merge($this->plain->Herbs(), $this->forest->Herbs());
		$this->herbalBook = new HerbalBook();
	}

	protected function run(): void {
		$this->fixRegions();
		$this->fixPartyRecords();
	}

	private function fixRegions(): void {
		$plainForest = [$this->plain, $this->forest];
		foreach (Region::all() as $region) {
			$herbage   = $region->Herbage();
			$herb      = $herbage?->Herb();
			$landscape = $region->Landscape();
			if ($landscape instanceof Navigable && !$herb) {
				continue;
			}
			if (in_array($landscape, $plainForest) && in_array($herb, $this->pfHerbs)) {
				continue;
			}
			if (in_array($herb, $landscape->Herbs())) {
				continue;
			}

			$newHerb    = $this->generator->setRegion($region)->getHerb();
			$occurrence = $herbage->Occurrence();
			$herbage    = new Herbage($newHerb);
			$region->setHerbage($herbage->setOccurrence($occurrence));
			$this->herbalBook->record($region, $herbage);
			Lemuria::Log()->critical('Region ' . $region . ' (' . $landscape .') grows ' . $herb . ' - changed to ' . $newHerb . ' (' . $occurrence . ').');
		}
	}

	private function fixPartyRecords(): void {
		foreach (Party::all() as $party) {
			if ($party->Type() === Type::Player && !$party->hasRetired()) {
				$herbalBook = $party->HerbalBook();
				foreach ($this->herbalBook as $region) {
					if ($herbalBook->has($region->Id())) {
						$herbalBook->record($region, $this->herbalBook->getHerbage($region), $herbalBook->getVisit($region)->Round());
						Lemuria::Log()->debug('Herbal book of party ' . $party . ' updated for region ' . $region . '.');
					}
				}
			}
		}
	}
}
