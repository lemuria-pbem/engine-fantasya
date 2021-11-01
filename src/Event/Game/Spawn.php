<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\Namer;
use Lemuria\Engine\Fantasya\Factory\Namer\RaceNamer;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\Combat;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Skeleton;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

/**
 * This event gives birth to a new NPC or monster unit.
 */
final class Spawn extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public const PARTY = 'party';

	public const REGION = 'region';

	public const RACE = 'race';

	public const SIZE = 'size';

	private const PARTY_ID = [Party::NPC => 'n', Party::MONSTER => 'm'];

	private const NAMER = [
		'' => RaceNamer::class
	];

	private const BATTLE_ROW = [
		''              => Combat::FRONT,
		Goblin::class   => Combat::CAREFUL,
		Skeleton::class => Combat::AGGRESSIVE,
		Zombie::class   => Combat::AGGRESSIVE
	];

	private Party $party;

	private Region $region;

	private Gang $gang;

	private Dictionary $dictionary;

	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
		$this->dictionary = new Dictionary();
	}

	public function setOptions(array $options): Spawn {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$type = $this->getOption(self::PARTY, 'int');
		$race = $this->getOption(self::RACE, 'string');
		$size = $this->getOption(self::SIZE, 'int');
		if (!isset(self::PARTY_ID[$type])) {
			throw new \InvalidArgumentException($type);
		}

		$this->party  = Party::get(Id::fromId(self::PARTY_ID[$type]));
		$this->region = Region::get(new Id($this->getOption(self::REGION, 'int')));
		$this->gang   = new Gang(self::createRace($race), $size);
	}

	protected function run(): void {
		$unit = new Unit();
		$unit->setId(Lemuria::Catalog()->nextId(Catalog::UNITS));
		$unit->setRace($this->gang->Race())->setSize($this->gang->Count());
		$this->party->People()->add($unit);
		$this->region->Residents()->add($unit);

		$race = $this->gang->Race()::class;

		$namerClass = self::NAMER[$race] ?? self::NAMER[''];
		/** @var Namer $namer */
		$namer = new $namerClass();
		$namer->name($unit);

		$battleRow = self::BATTLE_ROW[$race] ?? self::BATTLE_ROW[''];
		$unit->setBattleRow($battleRow);

		$this->initActivityProtocol($unit);
		Lemuria::Log()->debug('A new unit of ' . $this->gang . ' has been spawned in ' . $this->region . '.');
	}
}
