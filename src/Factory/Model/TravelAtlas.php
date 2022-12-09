<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Effect\Cartography;
use Lemuria\Engine\Fantasya\Effect\FarsightEffect;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\World\Atlas;
use Lemuria\SortMode;

final class TravelAtlas extends Atlas
{
	/**
	 * @var array(int=>Visibility)
	 */
	private array $visibility = [];

	public function __construct(private readonly Party $party) {
		parent::__construct();
	}

	public function forRound(int $round): TravelAtlas {
		$this->clear();
		$this->visibility = [];

		$census  = new Census($this->party);
		$outlook = new Outlook($census);
		foreach ($census->getAtlas() as $id => $region /* @var Region $region */) {
			$this->add($region);
			$this->visibility[$id] = Visibility::WITH_UNIT;

			$panorama = $outlook->getPanorama($region);
			foreach ($panorama as $neighbour /* @var Region $neighbour */) {
				$id         = $neighbour->Id()->Id();
				$visibility = $panorama->getVisibility($neighbour);
				if (!isset($this->visibility[$id])) {
					$this->add($neighbour);
					$this->visibility[$id] = $visibility;
				} elseif ($visibility->value > $this->visibility[$id]->value) {
					$this->visibility[$id] = $visibility;
				}
			}
		}

		$chronicle = $this->party->Chronicle();
		foreach ($chronicle as $id => $region /* @var Region $region */) {
			if ($chronicle->getVisit($region)->Round() === $round) {
				$visibility = Visibility::TRAVELLED;
				if ($this->hasCartography($region)) {
					$visibility = Visibility::NEIGHBOUR;
				}
				if ($this->hasFarsight($region)) {
					$visibility = Visibility::FARSIGHT;
				}
				if (!isset($this->visibility[$id])) {
					$this->add($region);
					$this->visibility[$id] = $visibility;
				} elseif ($this->visibility[$id]->value < $visibility->value) {
					$this->visibility[$id] = $visibility;
				}

				if ($visibility === Visibility::TRAVELLED) {
					$visibility = Visibility::NEIGHBOUR;
					foreach (Lemuria::World()->getNeighbours($region) as $neighbour) {
						$id = $neighbour->Id()->Id();
						if (!isset($this->visibility[$id])) {
							$this->add($neighbour);
							$this->visibility[$id] = $visibility;
						} elseif ($this->visibility[$id]->value < $visibility->value) {
							$this->visibility[$id] = $visibility;
						}
					}
				}
			}
		}

		$this->sort(SortMode::NORTH_TO_SOUTH);
		return $this;
	}

	public function getVisibility(Region $region): Visibility {
		return $this->visibility[$region->Id()->Id()] ?? Visibility::UNKNOWN;
	}

	public function setVisibility(Region $region, Visibility $visibility): TravelAtlas {
		$this->visibility[$region->Id()->Id()] = $visibility;
		return $this;
	}

	private function hasFarsight(Region $region): bool {
		$effect = new FarsightEffect(State::getInstance());
		$effect = Lemuria::Score()->find($effect->setRegion($region));
		if ($effect instanceof FarsightEffect) {
			return $effect->Parties()->has($this->party->Id());
		}
		return false;
	}

	private function hasCartography(Region $region): bool {
		$effect = new Cartography(State::getInstance());
		$effect = Lemuria::Score()->find($effect->setRegion($region));
		if ($effect instanceof Cartography) {
			return $effect->Parties()->has($this->party->Id());
		}
		return false;
	}
}
