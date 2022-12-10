<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Factory\NavigationTrait;
use Lemuria\Engine\Fantasya\Factory\TravelTrait;
use Lemuria\Engine\Fantasya\Message\Vessel\DriftDamageMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\DriftMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Vessel;

/**
 * Ships on sea that cannot be controlled anymore will drift with the wind.
 *
 * - If captain or crew lose their Navigation talent below the required level, the ship cannot be controlled anymore.
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
	use TravelTrait;

	private Talent $navigation;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
		$this->navigation = self::createTalent(Navigation::class);
	}

	protected function prepareAction(): void {
		parent::prepareAction();
		$this->state->isTravelling = true;
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Vessel) as $vessel /* @var Vessel $vessel */) {
			$this->vessel = $vessel;
			$region       = $vessel->Region();
			if ($region->Landscape() instanceof Ocean) {
				if ($this->hasSufficientCrew()) {
					continue;
				}

				$neighbours = $this->getNeighbourRegions($region);
				$coastline  = $this->getCoastline($neighbours);
				if (count($coastline) > 0) {
					$directions = $coastline->getDirections();
				} else {
					$directions = $neighbours->getDirections();
				}
				$direction = $directions[array_rand($directions)];

				/** @var Region $driftRegion */
				$driftRegion = $neighbours[$direction];
				$landscape   = $driftRegion->Landscape();
				if ($landscape instanceof Ocean || $this->canSailTo($driftRegion)) {
					$this->moveVessel($driftRegion);
					$this->message(DriftMessage::class, $vessel)->p($direction->value);
				} else {
					$damage = rand(1, 15) / 100;
					$vessel->setCompletion(max(0, $vessel->Completion() - $damage));
					$this->message(DriftDamageMessage::class, $vessel)->e($driftRegion);
				}
			}
		}
	}
}
