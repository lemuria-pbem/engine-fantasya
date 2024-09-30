<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Lake;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Region;

/**
 * This event converts the landscape of lakes to the new Lake.
 */
final class ConvertLakes extends AbstractEvent
{
	use BuilderTrait;

	/**
	 * @type array<int, int>
	 */
	private const array BIG_LAKES = [
		2450 => 1, 2451 => 1, 2519 => 1,
		2504 => 2, 2505 => 2, 2574 => 2, 2575 => 2, 2644 => 2,
		3269 => 3, 3270 => 3,
		3364 => 4, 3365 => 4, 3366 => 4, 3433 => 4, 3434 => 4, 3435 => 4, 3436 => 4, 3502 => 4, 3503 => 4,
		3504 => 4, 3505 => 4, 3506 => 4, 3571 => 4, 3572 => 4, 3573 => 4, 3574 => 4, 3575 => 4, 3641 => 4
	];

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		$lake  = self::createLandscape(Lake::class);
		$ocean = self::createLandscape(Ocean::class);
		foreach (Region::all() as $region) {
			if ($region->Landscape() === $ocean) {
				$id = $region->Id();
				$l  = self::BIG_LAKES[$id->Id()] ?? 0;
				if ($l) {
					$region->setLandscape($lake);
					$this->renameLake($region);
					Lemuria::Log()->debug('Region ' . $id . ' is now part of the big lake ' . $l . '.');
				} elseif ($this->isLake($region)) {
					$region->setLandscape($lake);
					$this->renameLake($region);
					Lemuria::Log()->debug('Region ' . $id . ' is converted to a Lake.');
				}
			}
		}
	}

	private function isLake(Region $region): bool {
		foreach (Lemuria::World()->getNeighbours($region) as $region) {
			/** @var Region $region */
			if ($region->Landscape() instanceof Ocean) {
				return false;
			}
		}
		return true;
	}

	private function renameLake(Region $region): void {
		if ($region->Name() === 'Ozean') {
			$region->setName('See');
		}
	}
}
