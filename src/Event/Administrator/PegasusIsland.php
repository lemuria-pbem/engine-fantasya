<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;

/**
 * This event removes monsters from regions that should not be at that landscape.
 */
final class PegasusIsland extends AbstractEvent
{
	use BuilderTrait;

	private const string REGION = '2pc';

	private const string NAME = 'Arvallon';

	private const int PEGASI = 100;

	private const int MAX_TREES = 300;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		// Change lake to plain.
		$island    = Region::get(Id::fromId(self::REGION));
		$resources = $island->Resources();
		$plain     = self::createLandscape(Plain::class);
		$wood      = self::createCommodity(Wood::class);
		if ($island->Landscape() !== $plain) {
			$island->setLandscape($plain)->setName(self::NAME);
			$pegasus = self::createCommodity(Pegasus::class);
			$resources->add(new Quantity($pegasus, self::PEGASI));
			$resources->add(new Quantity($wood, self::MAX_TREES));
			Lemuria::Log()->debug('Pegasus Island has been established.');
		}
		// Remove excess trees.
		$trees     = $resources[$wood]->Count();
		$excess    = max(0, $trees - self::MAX_TREES);
		if ($excess > 0) {
			$quantity = new Quantity($wood, $trees);
			$resources->remove($quantity);
			Lemuria::Log()->debug($quantity . ' have been removed on the island.');
		}
	}
}
