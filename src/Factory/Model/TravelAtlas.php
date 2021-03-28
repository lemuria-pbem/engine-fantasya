<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Census;
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
					$this->visibility[$id] = self::NEIGHBOUR;
				}
			}
		}

		$chronicle = $this->party->Chronicle();
		foreach ($chronicle as $id => $region /* @var Region $region */) {
			if (!array_key_exists($id, $this->visibility) || $this->visibility[$id] === self::NEIGHBOUR) {
				$visibility            = $chronicle->getVisit($region)->Round() === $round ? self::TRAVELLED : self::HISTORIC;
				$this->visibility[$id] = $visibility;
			}
		}

		$this->sort();
		return $this;
	}

	#[Pure] public function getVisibility(Region $region): int {
		return $this->visibility[$region->Id()->Id()] ?? self::UNKNOWN;
	}
}
