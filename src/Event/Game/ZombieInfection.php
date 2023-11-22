<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use function Lemuria\randChance;
use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Region\Event\ZombieInfectionMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;

/**
 * Zombies in a region attack peasants and turn them into new zombies.
 */
final class ZombieInfection extends AbstractEvent
{
	use BuilderTrait;
	use MessageTrait;
	use OptionsTrait;

	public final const string REGION = 'region';

	public final const string INFECT = 'infect';

	public final const string CHANCE = 'chance';

	private const int MAX_ZOMBIE_COUNT = 30;

	private const int MAX_ZOMBIE_SIZE = 1000;

	private const int MAX_ZOMBIE_REGIONS = 25;

	private Region $region;

	private float $infect;

	private float $chance;

	private Party $party;

	private Monster $zombie;

	private Commodity $peasant;

	public static function addZombieInfections(array &$events): void {
		$party   = State::getInstance()->getTurnOptions()->Finder()->Party()->findByRace(self::createRace(Zombie::class));
		$zombies = $party->People();
		if ($zombies->count() >= self::MAX_ZOMBIE_COUNT || $zombies->Size() >= self::MAX_ZOMBIE_SIZE) {
			Lemuria::Log()->debug('Skipping ZombieInfection.');
			return;
		}

		$regions = [];
		foreach ($zombies as $unit) {
			if ($unit->Race() instanceof Zombie && $unit->Size() > 0) {
				$region = $unit->Region();
				$id     = $region->Id()->Id();
				if (isset($regions[$id])) {
					$regions[$id] += $unit->Size();
				} else {
					$regions[$id] = $unit->Size();
				}
			}
		}
		$count = count($regions);
		if ($count >= self::MAX_ZOMBIE_REGIONS) {
			Lemuria::Log()->debug('Skipping ZombieInfection.');
			return;
		}

		Lemuria::Log()->debug('Adding ZombieInfection in ' . $count . ' regions.');
		$state = State::getInstance();
		foreach (array_keys($regions) as $id) {
			$event  = new self($state);
			$infect = (1.0 + (randInt(0, 40) - 20) / 100) * 0.01;
			$event->setOptions([self::REGION => $id, self::INFECT => $infect, self::CHANCE => 0.01]);
			$events[] = $event;
		}
	}

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->party   = $state->getTurnOptions()->Finder()->Party()->findByRace(self::createRace(Zombie::class));
		$this->zombie  = self::createMonster(Zombie::class);
		$this->peasant = self::createCommodity(Peasant::class);
	}

	public function setOptions(array $options): ZombieInfection {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->region = Region::get($this->getIdOption(self::REGION));
		$this->infect = $this->getOption(self::INFECT, 'float');
		$this->chance = $this->getOption(self::CHANCE, 'float');
	}

	protected function run(): void {
		$resources = $this->region->Resources();
		$peasants  = $resources[$this->peasant]->Count();
		$zombies   = [];
		foreach ($this->region->Residents() as $unit) {
			if ($unit->Party() === $this->party && $unit->Race() === $this->zombie) {
				$zombies[] = $unit;
			}
		}

		foreach ($zombies as $zombie) {
			$chance = $zombie->Size() * $this->chance;
			if (randChance($chance)) {
				$size      = (int)ceil($this->infect * $peasants);
				$peasants -= $size;
				$remove    = new Quantity($this->peasant, $size);
				$resources->remove($remove);
				$create = new Create($this->party, $this->region);
				$create->add(new Gang($this->zombie, $size))->act();
				$this->message(ZombieInfectionMessage::class, $this->region)->i($remove);
			}
		}
	}
}
