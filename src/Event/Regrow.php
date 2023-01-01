<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use function Lemuria\getClass;
use function Lemuria\randElement;
use Lemuria\Engine\Fantasya\Message\Region\RegrowMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Calendar\Season;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Herb;
use Lemuria\Model\Fantasya\Commodity\Herb\Bubblemorel;
use Lemuria\Model\Fantasya\Commodity\Herb\Bugleweed;
use Lemuria\Model\Fantasya\Commodity\Herb\CaveLichen;
use Lemuria\Model\Fantasya\Commodity\Herb\CobaltFungus;
use Lemuria\Model\Fantasya\Commodity\Herb\Elvendear;
use Lemuria\Model\Fantasya\Commodity\Herb\FjordFungus;
use Lemuria\Model\Fantasya\Commodity\Herb\Flatroot;
use Lemuria\Model\Fantasya\Commodity\Herb\Gapgrowth;
use Lemuria\Model\Fantasya\Commodity\Herb\IceBegonia;
use Lemuria\Model\Fantasya\Commodity\Herb\Knotroot;
use Lemuria\Model\Fantasya\Commodity\Herb\Mandrake;
use Lemuria\Model\Fantasya\Commodity\Herb\Owlsgaze;
use Lemuria\Model\Fantasya\Commodity\Herb\Peyote;
use Lemuria\Model\Fantasya\Commodity\Herb\Rockweed;
use Lemuria\Model\Fantasya\Commodity\Herb\Sandreeker;
use Lemuria\Model\Fantasya\Commodity\Herb\Snowcrystal;
use Lemuria\Model\Fantasya\Commodity\Herb\SpiderIvy;
use Lemuria\Model\Fantasya\Commodity\Herb\TangyTemerity;
use Lemuria\Model\Fantasya\Commodity\Herb\Waterfinder;
use Lemuria\Model\Fantasya\Commodity\Herb\WhiteHemlock;
use Lemuria\Model\Fantasya\Commodity\Herb\Windbag;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Herbage;
use Lemuria\Model\Fantasya\Landscape\Desert;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Glacier;
use Lemuria\Model\Fantasya\Landscape\Highland;
use Lemuria\Model\Fantasya\Landscape\Mountain;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Landscape\Swamp;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Region;

/**
 * Herbs grow in the spring and summer and reduce in winter.
 */
final class Regrow extends AbstractEvent
{
	use BuilderTrait;

	private const MINIMUM = 0.01;

	private const GROW = 0.04;

	private const SHRINK = -0.05;

	private const MIGRATE = -0.05;

	private const SWITCH = 0.1;

	private const HERBS = [
		Plain::class => [
			Glacier::class  => TangyTemerity::class, Desert::class => Flatroot::class,
			Highland::class => TangyTemerity::class, Swamp::class  => Owlsgaze::class,
			Mountain::class => Flatroot::class,      Forest::class => Owlsgaze::class
		],
		Forest::class => [
			Glacier::class  => CobaltFungus::class, Desert::class => SpiderIvy::class,
			Highland::class => Elvendear::class,    Swamp::class  => CobaltFungus::class,
			Mountain::class => SpiderIvy::class,    Plain::class  => Elvendear::class
		],
		Swamp::class => [
			Glacier::class  => Knotroot::class,    Desert::class   => Bubblemorel::class,
			Highland::class => Bubblemorel::class, Mountain::class => Knotroot::class,
			Plain::class    => Bugleweed::class,   Forest::class   => Bugleweed::class
		],
		Highland::class => [
			Glacier::class => Windbag::class,       Desert::class => Mandrake::class,
			Swamp::class   => FjordFungus::class, Mountain::class => Windbag::class,
			Plain::class   => Mandrake::class,      Forest::class => FjordFungus::class
		],
		Desert::class => [
			Glacier::class => Sandreeker::class,  Highland::class => Peyote::class,
			Swamp::class   => Waterfinder::class, Mountain::class => Sandreeker::class,
			Plain::class   => Peyote::class,      Forest::class   => Waterfinder::class
		],
		Mountain::class => [
			Glacier::class  => Gapgrowth::class, Desert::class => Rockweed::class,
			Highland::class => Gapgrowth::class, Swamp::class  => CaveLichen::class,
			Plain::class    => Rockweed::class,  Forest::class => CaveLichen::class
		],
		Glacier::class => [
			Desert::class => IceBegonia::class,  Highland::class => WhiteHemlock::class,
			Swamp::class  => Snowcrystal::class, Mountain::class => WhiteHemlock::class,
			Plain::class  => IceBegonia::class,  Forest::class   => Snowcrystal::class
		]
	];

	private float $rate;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function initialize(): void {
		$this->rate = 1.0 + match (Lemuria::Calendar()->Season()) {
			Season::Spring, Season::Summer => self::GROW,
			Season::Fall                   => 0.0,
			Season::Winter                 => self::SHRINK
		};
		Lemuria::Log()->debug('Herbage grow rate is ' . $this->rate . '.');
	}

	/**
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 */
	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Location) as $region /* @var Region $region */) {
			$landscape = $region->Landscape();
			if ($landscape instanceof Navigable) {
				continue;
			}
			if (!$region->Herbage()) {
				$this->initHerbage($region);
				continue;
			}
			if ($landscape instanceof Plain || $landscape instanceof Forest) {
				$this->growInPlainForest($region);
				continue;
			}
			$this->grow($region);
		}
	}

	private function initHerbage(Region $region, float $occurrence = self::SWITCH): void {
		$landscape  = $region->Landscape();
		$herbs      = self::HERBS[$landscape::class];
		$neighbours = $this->getNeighbourLandscapes($region);
		$class      = null;
		foreach ($herbs as $neighbour => $herb) {
			if (isset($neighbours[$neighbour])) {
				$class = $herb;
				break;
			}
		}

		if (!$class) {
			$herbs = $landscape->Herbs();
			$class = randElement($herbs)::class;
		}

		/** @var Herb $herb */
		$herb    = self::createCommodity($class);
		$herbage = new Herbage($herb);
		$region->setHerbage($herbage->setOccurrence($occurrence));
		Lemuria::Log()->debug('New herbage for region ' . $region . ' is ' . getClass($herb) . ' (' . $occurrence . ').');
	}

	private function growInPlainForest(Region $region): void {
		$herbage   = $region->Herbage();
		$landscape = $region->Landscape();
		if (in_array($herbage->Herb(), $landscape->Herbs())) {
			$this->grow($region);
		} else {
			$occurrence = $herbage->Occurrence();
			if ($occurrence < self::SWITCH) {
				$this->initHerbage($region, $occurrence);
			} else {
				$occurrence = 1.0 + self::MIGRATE * $occurrence;
				$herbage->setOccurrence($occurrence);
				Lemuria::Log()->debug('Herbage in region ' . $region . ' shrinks to ' . $occurrence . '.');
			}
		}
	}

	private function grow(Region $region): void {
		$herbage    = $region->Herbage();
		$occurrence = max(self::MINIMUM, min(1.0, $this->rate * $herbage->Occurrence()));
		$herbage->setOccurrence($occurrence);
		$this->message(RegrowMessage::class, $region)->s($herbage->Herb())->p($occurrence);
	}

	private function getNeighbourLandscapes(Region $region): array {
		$neighbours = [];
		foreach (Lemuria::World()->getNeighbours($region) as $neighbour /* @var Region $neighbour */) {
			$landscape = $neighbour->Landscape();
			$neighbours[$landscape::class] = true;
		}
		return $neighbours;
	}
}
