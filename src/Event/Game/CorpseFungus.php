<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Effect\CorpseFungusEffect;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Region\Event\CorpseFungusHereMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

/**
 * The Corpse Fungus infests the zombies in a region, devours the flesh from their bones, and turns them into skeletons.
 */
final class CorpseFungus extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const string REGION = 'region';

	private const float CHANCE = 0.75;

	private Region $region;

	private Monster $zombie;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->zombie = self::createMonster(Zombie::class);
	}

	public function setOptions(array $options): CorpseFungus {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->region = Region::get($this->getIdOption(self::REGION));
	}

	protected function run(): void {
		$this->message(CorpseFungusHereMessage::class, $this->region);
		foreach ($this->region->Residents() as $unit) {
			if ($unit->Race() === $this->zombie) {
				if (!$unit->Construction() && !$unit->Vessel()) {
					if (randChance(self::CHANCE)) {
						$this->setEffect($unit);
					}
				}
			}
		}
	}

	private function setEffect(Unit $unit): void {
		$effect = new CorpseFungusEffect($this->state);
		Lemuria::Score()->add($effect->setUnit($unit));
		$this->state->injectIntoTurn($effect);
		Lemuria::Log()->debug($unit . ' is infected with the Corpse Fungus.');
	}
}
