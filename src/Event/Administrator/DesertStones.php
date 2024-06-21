<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Stone;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Desert;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;

/**
 * This event adds a bunch of stones to the resources of every desert.
 */
final class DesertStones extends AbstractEvent
{
	use BuilderTrait;

	private const array RANGE = [9, 17];

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function run(): void {
		$desert = self::createLandscape(Desert::class);
		$stone  = self::createCommodity(Stone::class);
		foreach (Region::all() as $region) {
			if ($region->Landscape() === $desert) {
				$resources = $region->Resources();
				if (!isset($resources[$stone])) {
					$stones = new Quantity($stone, $this->calculateStone());
					$resources->add($stones);
					Lemuria::Log()->debug('Desert ' . $region . ' now has ' . $stones . '.');
				}
			}
		}
	}

	private function calculateStone(): int {
		return randInt(...self::RANGE);
	}
}
