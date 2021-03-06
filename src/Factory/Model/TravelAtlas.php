<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\World\Atlas;

final class TravelAtlas extends Atlas
{
	public const UNKNOWN = 0;

	public const HISTORIC = 1;

	public const NEIGHBOUR = 2;

	public const TRAVELLED = 3;

	public const WITH_UNIT = 4;

	private array $visibility = [];

	#[Pure] public function __construct(private Party $party) {
		parent::__construct();
	}

	public function forRound(int $round): TravelAtlas {
		$this->clear();
		$this->visibility = [];

		$census  = new Census($this->party);
		$outlook = new Outlook($census);
		foreach ($census->getAtlas() as $id => $region /* @var Region $region */) {
			$this->add($region);
			$this->visibility[$id] = self::WITH_UNIT;

			foreach ($outlook->Panorama($region) as $neighbour /* @var Region $neighbour */) {
				$id = $neighbour->Id()->Id();
				if (!isset($this->visibility[$id])) {
					$this->add($neighbour);
					$this->visibility[$id] = self::NEIGHBOUR;
				}
			}
		}

		$chronicle = $this->party->Chronicle();
		foreach ($chronicle as $id => $region /* @var Region $region */) {
			if ($chronicle->getVisit($region)->Round() === $round) {
				if (!isset($this->visibility[$id])) {
					$this->add($region);
					$this->visibility[$id] = self::TRAVELLED;
				} elseif ($this->visibility[$id] === self::NEIGHBOUR) {
					$this->visibility[$id] = self::TRAVELLED;
				}
			}
		}

		$this->sort(Atlas::NORTH_TO_SOUTH);
		return $this;
	}

	#[Pure] public function getVisibility(Region $region): int {
		return $this->visibility[$region->Id()->Id()] ?? self::UNKNOWN;
	}
}
