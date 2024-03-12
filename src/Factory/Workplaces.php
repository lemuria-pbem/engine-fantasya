<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Building\HuntingLodge;
use Lemuria\Model\Fantasya\Building\MushroomCave;
use Lemuria\Model\Fantasya\Building\Oasis;
use Lemuria\Model\Fantasya\Building\Plantation;
use Lemuria\Model\Fantasya\Landscape\Mountain;

final class Workplaces
{
	use BuilderTrait;

	public final const int CAMEL = 1;

	public final const int ELEPHANT = 5;

	public final const int HORSE = 1;

	public final const int PEGASUS = 1;

	public final const int TREE = 10;

	private const float TREE_RATE = 0.2;

	private static ?int $mountainWorkplaces = null;

	public function getUsed(int $horse = 0, int $pegasus = 0, int $camel = 0, int $elephant = 0, int $tree = 0): int {
		return $horse * self::HORSE + $pegasus * self::PEGASUS + $camel * self::CAMEL + $elephant * self::ELEPHANT + $tree * self::TREE;
	}

	public function getAdditional(Building $building, int $count, int $maxSize, int $workplaces, int $trees): int {
		return match ($building::class) {
			HuntingLodge::class => (int)floor(self::TREE_RATE * $trees * self::TREE),
			MushroomCave::class => (int)floor(min(1.0, $maxSize / 100) * self::mountainWorkplaces()),
			Plantation::class   => (int)floor(min(1.0, $maxSize / 100) * $workplaces),
			Oasis::class        => $count * $workplaces,
			default             => 0
		};
	}

	private static function mountainWorkplaces(): int {
		if (!self::$mountainWorkplaces) {
			self::$mountainWorkplaces = self::createLandscape(Mountain::class)->Workplaces();
		}
		return self::$mountainWorkplaces;
	}
}
