<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Command\Create\Herb as HerbGrowing;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\Factory\Model\Herb;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Message\Unit\GreenhousingMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Greenhouse;
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
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Inhabitants;
use Lemuria\Model\Fantasya\Landscape;
use Lemuria\Model\Fantasya\Landscape\Desert;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Glacier;
use Lemuria\Model\Fantasya\Landscape\Highland;
use Lemuria\Model\Fantasya\Landscape\Mountain;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Landscape\Swamp;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Herballore;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\SortMode;

/**
 * Animals grow in breeding farms.
 */
final class Greenhousing extends AbstractEvent
{
	use BuilderTrait;
	use WorkloadTrait;

	public final const array LANDSCAPES = [
		Desert::class   => [Peyote::class, Sandreeker::class, Waterfinder::class],
		Forest::class   => [CobaltFungus::class, Elvendear::class, SpiderIvy::class],
		Glacier::class  => [IceBegonia::class, Snowcrystal::class, WhiteHemlock::class],
		Highland::class => [FjordFungus::class, Mandrake::class, Windbag::class],
		Mountain::class => [CaveLichen::class, Gapgrowth::class, Rockweed::class],
		Plain::class    => [Flatroot::class, Owlsgaze::class, TangyTemerity::class],
		Swamp::class    => [Bubblemorel::class, Bugleweed::class, Knotroot::class]
	];

	private const array RATE = [
		Desert::class   => [1.0, 0.3, 0.2, 0.5, 0.3, 0.5, 0.2],
		Forest::class   => [0.3, 1.0, 0.3, 0.5, 0.5, 0.5, 0.5],
		Glacier::class  => [0.2, 0.3, 1.0, 0.3, 0.5, 0.3, 0.5],
		Highland::class => [0.5, 0.5, 0.3, 1.0, 0.5, 0.5, 0.3],
		Mountain::class => [0.3, 0.5, 0.5, 0.5, 1.0, 0.3, 0.3],
		Plain::class    => [0.5, 0.5, 0.3, 0.5, 0.3, 1.0, 0.5],
		Swamp::class    => [0.2, 0.5, 0.5, 0.3, 0.3, 0.5, 1.0]
	];

	private Talent $herballore;

	private Unit $unit;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
		$this->context    = new Context($state);
		$this->herballore = self::createTalent(Herballore::class);
	}

	protected function run(): void {
		foreach (Construction::all() as $construction) {
			$building = $construction->Building();
			if ($building instanceof Greenhouse && $this->isMaintained($construction)) {
				$inhabitants = $construction->Inhabitants();
				$knowledge   = $this->calculateKnowledge($inhabitants);
				$production  = (int)floor($knowledge / HerbGrowing::LEVEL);
				$herbs       = $this->determineHerbs($inhabitants);
				$rates       = $this->determineRates($construction->Region()->Landscape());
				$grows       = $this->determineGrows($herbs, $rates, $production, $construction->Size());
				$owner       = $inhabitants->Owner();
				foreach ($grows as $grow) {
					$this->message(GreenhousingMessage::class, $owner)->i($grow);
				}
			}
		}
	}

	private function calculateKnowledge(Inhabitants $inhabitants): int {
		$knowledge = 0;
		foreach ($inhabitants as $unit) {
			$knowledge += $unit->Size() * $this->context->getCalculus($unit)->knowledge($this->herballore)->Level();
		}
		return $knowledge;
	}

	private function determineHerbs(Inhabitants $inhabitants): Resources {
		$herbs = new Resources();
		foreach ($inhabitants as $unit) {
			foreach ($unit->Inventory() as $quantity) {
				$herb = $quantity->Commodity();
				if ($herb instanceof Herb) {
					$herbs->add(new Quantity($herb, $quantity->Count()));
				}
			}
		}
		return $herbs;
	}

	/**
	 * @return array<string, float>
	 */
	private function determineRates(Landscape $landscape): array {
		$rates = [];
		$index = array_flip(array_keys(self::LANDSCAPES));
		$rate  = self::RATE[$landscape::class];
		foreach (self::LANDSCAPES as $where => $herbs) {
			$i = $index[$where];
			foreach ($herbs as $herb) {
				$rates[$herb] = $rate[$i];
			}
		}
		return $rates;
	}

	/**
	 * @param array<string, float> $rates
	 */
	private function determineGrows(Resources $herbs, array $rates, int $production, int $size): Resources {
		$herbs->sort(SortMode::ByCount);
		$herbs->rewind();
		$grows = new Resources();
		$g     = min($herbs->count(), $production, $size);
		if ($g > 0) {
			$n = (int)floor($production / $g);
			$r = $production % $g;
			for ($i = 0; $i < $g; $i++) {
				$current = $herbs->current();
				$herb    = $current->Commodity();
				$count   = $n + ($r-- > 0 ? 1 : 0);
				$rate    = $rates[$herb::class];
				$count   = max(1, (int)floor($rate * $count));
				$grows->add(new Quantity($herb, $count));
				$herbs->next();
			}
		}
		return $grows;
	}

	private function isMaintained(Construction $construction): bool {
		$effect = new Unmaintained($this->state);
		return Lemuria::Score()->find($effect->setConstruction($construction)) === null;
	}
}
