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
use Lemuria\Model\Location;
use Lemuria\Model\World\Atlas;
use Lemuria\SortMode;

final class TravelAtlas extends Atlas
{
	/**
	 * @var array<int, Visibility>
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
		foreach ($census->getAtlas() as $id => $region) {
			$this->add($region);
			$this->visibility[$id] = Visibility::WithUnit;

			$panorama = $outlook->getPanorama($region);
			foreach ($panorama as $neighbour) {
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
		foreach ($chronicle as $id => $region) {
			if ($chronicle->getVisit($region)->Round() === $round) {
				$visibility = Visibility::Travelled;
				if ($this->hasCartography($region)) {
					$visibility = Visibility::Neighbour;
				}
				if ($this->hasFarsight($region)) {
					$visibility = Visibility::Farsight;
				}
				if (!isset($this->visibility[$id])) {
					$this->add($region);
					$this->visibility[$id] = $visibility;
				} elseif ($this->visibility[$id]->value < $visibility->value) {
					$this->visibility[$id] = $visibility;
				}

				if ($visibility === Visibility::Travelled) {
					$visibility = Visibility::Neighbour;
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

		$this->sort(SortMode::NorthToSouth);
		return $this;
	}

	public function getVisibility(Location $region): Visibility {
		return $this->visibility[$region->Id()->Id()] ?? Visibility::Unknown;
	}

	public function setVisibility(Location $region, Visibility $visibility): TravelAtlas {
		$this->visibility[$region->Id()->Id()] = $visibility;
		return $this;
	}

	private function hasFarsight(Location $region): bool {
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
