<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Factory\Model\Season;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Region;

/**
 * Forests grow in the spring.
 */
final class Regrow extends AbstractEvent
{
	use BuilderTrait;

	private const GROW = 0.035;

	private const SHRINK = -0.025;

	private float $rate;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function initialize(): void {
		$this->rate = match (Lemuria::Calendar()->Season()) {
			Season::SPRING, Season::SUMMER => self::GROW,
			Season::FALL                   => 0.0,
			Season::WINTER                 => self::SHRINK
		};
	}

	protected function run(): void {
		Lemuria::Log()->debug('Herbage grow rate is ' . $this->rate . '.');
		foreach (Lemuria::Catalog()->getAll(Catalog::LOCATIONS) as $region /* @var Region $region */) {
			$landscape = $region->Landscape();
			if ($landscape instanceof Ocean) {
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

	protected function initHerbage(Region $region): void {

	}

	protected function growInPlainForest(Region $region): void {

	}

	protected function grow(Region $region): void {

	}
}
