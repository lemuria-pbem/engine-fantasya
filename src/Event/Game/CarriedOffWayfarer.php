<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\BuilderTrait as CarcassBuilderTrait;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;

/**
 * This event places a carcass.
 */
final class CarriedOffWayfarer extends AbstractEvent
{
	use BuilderTrait;
	use CarcassBuilderTrait;
	use OptionsTrait;

	public final const REGION = 'region';

	public final const RACE = 'race';

	public final const INVENTORY = 'inventory';

	private Race $race;

	private Region $region;

	private Resources $inventory;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->inventory = new Resources();
	}

	public function setOptions(array $options): CarriedOffWayfarer {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->race   = self::createRace($this->getOption(self::RACE, 'string'));
		$this->region = Region::get($this->getIdOption(self::REGION));
		if ($this->hasOption(self::INVENTORY)) {
			foreach ($this->getOption(self::INVENTORY, 'array') as $commodity => $amount) {
				$this->inventory->add(new Quantity(self::createCommodity($commodity), $amount));
			}
		}
	}

	protected function run(): void {
		$unicum = $this->createNamedCarcass($this->race, $this->inventory);
		$this->region->Treasury()->add($unicum);
	}
}
