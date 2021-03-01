<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Factory\NavigationTrait;
use Lemuria\Engine\Lemuria\Message\Vessel\DriftDamageMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\DriftMessage;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Landscape\Ocean;
use Lemuria\Model\Lemuria\Region;
use Lemuria\Model\Lemuria\Talent;
use Lemuria\Model\Lemuria\Talent\Navigation;
use Lemuria\Model\Lemuria\Vessel;

/**
 * Ships on sea that cannot be controlled anymore will drift with the wind.
 *
 * - If captain or crew loose their Navigation talent below the required level, the ship cannot be controlled anymore.
 * - If the payload becomes too heavy for the ship, it cannot be controlled anymore.
 * - If the excess payload is not jettisoned overboard, the ship takes damage.
 * - Uncontrollable ships on open sea (no adjacent land region) will drift to a random direction.
 * - Uncontrollable ships near a coast will drift along the coastline or onto the shore.
 * - If a ship drifts onto a shore where it can usually land, it will do so; otherwise it takes damage and keeps its
 *   position.
 */
final class Drift extends AbstractEvent
{
	use BuilderTrait;
	use NavigationTrait;

	private Talent $navigation;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::MIDDLE);
		$this->navigation = self::createTalent(Navigation::class);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::VESSELS) as $vessel /* @var Vessel $vessel */) {
			$this->vessel = $vessel;
			$region       = $vessel->Region();
			if ($region->Landscape() instanceof Ocean) {
				if ($this->hasSufficientCrew()) {
					continue;
				}

				$neighbours = $this->getNeighbourRegions($region);
				$coastline  = $this->getCoastline($neighbours);
				if (count($coastline) > 0) {
					$direction = array_rand($coastline->getAll());
				} else {
					$direction = array_rand($neighbours->getAll());
				}
				/** @var Region $driftRegion */
				$driftRegion = $neighbours[$direction];
				$landscape   = $driftRegion->Landscape();
				if ($landscape instanceof Ocean || $this->canSailTo($landscape)) {
					$this->moveVessel($driftRegion);
					$this->message(DriftMessage::class, $vessel)->p($direction);
				} else {
					$damage = rand(1, 15) / 100;
					$vessel->setCompletion(max(0, $vessel->Completion() - $damage));
					$this->message(DriftDamageMessage::class, $vessel)->e($driftRegion);
				}
			}
		}
	}
}
