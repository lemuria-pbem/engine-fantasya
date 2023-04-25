<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Region\Event\ZombieInfectionMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
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

	public final const REGION = 'region';

	public final const INFECT = 'infect';

	public final const CHANCE = 'chance';

	private Region $region;

	private float $infect;

	private float $chance;

	private Party $party;

	private Monster $zombie;

	private Commodity $peasant;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->party   = Party::get(Id::fromId(Spawn::ZOMBIES));
		$this->zombie  = self::createMonster(Zombie::class);
		$this->peasant = self::createCommodity(Peasant::class);
	}

	public function setOptions(array $options): ZombieInfection {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->region = Region::get(new Id($this->getOption(self::REGION, 'int')));
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
