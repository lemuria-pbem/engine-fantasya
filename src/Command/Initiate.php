<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Factory\ActionTrait;
use Lemuria\Engine\Fantasya\Factory\Model\LemuriaNewcomer;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Message\Party\WelcomeMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity\Iron;
use Lemuria\Model\Fantasya\Commodity\Protection\Armor;
use Lemuria\Model\Fantasya\Commodity\Protection\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Protection\Mail;
use Lemuria\Model\Fantasya\Commodity\Protection\Woodshield;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Commodity\Stone;
use Lemuria\Model\Fantasya\Commodity\Weapon\Battleaxe;
use Lemuria\Model\Fantasya\Commodity\Weapon\Bow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Crossbow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Spear;
use Lemuria\Model\Fantasya\Commodity\Weapon\Sword;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Desert;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Highland;
use Lemuria\Model\Fantasya\Landscape\Mountain;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Landscape\Swamp;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Race;
use Lemuria\Model\Fantasya\Race\Aquan;
use Lemuria\Model\Fantasya\Race\Dwarf;
use Lemuria\Model\Fantasya\Race\Elf;
use Lemuria\Model\Fantasya\Race\Halfling;
use Lemuria\Model\Fantasya\Race\Human;
use Lemuria\Model\Fantasya\Race\Orc;
use Lemuria\Model\Fantasya\Race\Troll;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Talent\Archery;
use Lemuria\Model\Fantasya\Talent\Bladefighting;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Carriagemaking;
use Lemuria\Model\Fantasya\Talent\Constructing;
use Lemuria\Model\Fantasya\Talent\Crossbowing;
use Lemuria\Model\Fantasya\Talent\Entertaining;
use Lemuria\Model\Fantasya\Talent\Mining;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Talent\Quarrying;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Talent\Shipbuilding;
use Lemuria\Model\Fantasya\Talent\Spearfighting;
use Lemuria\Model\Fantasya\Talent\Stamina;
use Lemuria\Model\Fantasya\Talent\Taxcollecting;
use Lemuria\Model\Fantasya\Talent\Trading;
use Lemuria\Model\Fantasya\Talent\Woodchopping;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\World\LocationPicker;
use Lemuria\Model\World\SortMode;

/**
 * Introduce a Newcomer as a new Party.
 */
final class Initiate implements Command
{
	private const LANDSCAPES = [
		Aquan::class    => [Plain::class, Forest::class],
		Dwarf::class    => [Mountain::class, Highland::class],
		Elf::class      => [Forest::class, Plain::class, Highland::class, Swamp::class],
		Halfling::class => [Plain::class, Highland::class, Swamp::class],
		Human::class    => [Plain::class, Highland::class, Desert::class, Forest::class, Swamp::class],
		Orc::class      => [Plain::class, Highland::class, Mountain::class, Desert::class],
		Troll::class    => [Highland::class, Mountain::class, Desert::class, Plain::class]
	];

	private const KNOWLEDGE = [
		Aquan::class    => [Navigation::class => 12, Shipbuilding::class => 8, Spearfighting::class => 5],
		Dwarf::class    => [Mining::class => 12, Constructing::class => 8, Bladefighting::class => 5],
		Elf::class      => [Perception::class => 12, Camouflage::class => 8, Archery::class => 5],
		Halfling::class => [Entertaining::class => 12, Carriagemaking::class => 8, Spearfighting::class => 5],
		Human::class    => [Riding::class => 12, Trading::class => 8, Crossbowing::class => 5],
		Orc::class      => [Woodchopping::class => 12, Taxcollecting::class => 8, Bladefighting::class => 5],
		Troll::class    => [Quarrying::class => 12, Stamina::class => 8, Bladefighting::class => 5]
	];

	private const EVERYONE = [Silver::class => 10000, Wood::class => 500, Stone::class => 500, Iron::class => 500];

	private const INVENTORY = [
		Aquan::class    => [Spear::class => 1, Mail::class => 1, Woodshield::class => 1],
		Dwarf::class    => [Battleaxe::class => 1, Armor::class => 1, Ironshield::class => 1],
		Elf::class      => [Bow::class => 1, Mail::class => 1],
		Halfling::class => [Spear::class => 1, Mail::class => 1],
		Human::class    => [Crossbow::class => 1, Mail::class => 1],
		Orc::class      => [Sword::class => 1, Armor::class => 1, Woodshield::class => 1],
		Troll::class    => [Sword::class => 1, Armor::class => 1, Ironshield::class => 1]
	];

	use ActionTrait;
	use BuilderTrait;

	private int $id;

	public function __construct(private LemuriaNewcomer $newcomer) {
		$this->id = AbstractCommand::id();
	}

	#[Pure] public function __toString(): string {
		return 'INITIATE ' . $this->newcomer->Uuid();
	}

	/**
	 * @throws CommandException
	 */
	public function prepare(): Action {
		Lemuria::Log()->debug('Preparing command ' . $this . '.', ['command' => $this]);
		$this->prepareAction();
		return $this;
	}

	/**
	 * @throws CommandException
	 */
	public function execute(): Action {
		Lemuria::Log()->debug('Executing command ' . $this . '.', ['command' => $this]);
		$this->executeAction();
		return $this;
	}

	#[Pure] public function getId(): int {
		return $this->id;
	}

	#[Pure] public function getDelegate(): Command {
		return $this;
	}

	protected function run(): void {
		$race   = $this->pickRace();
		$origin = $this->pickOrigin($race);

		$party  = new Party($this->newcomer);
		$party->setId(Lemuria::Catalog()->nextId(Domain::PARTY));
		$party->setName($this->cleanName($this->newcomer->Name()));
		$party->setDescription($this->cleanDescription($this->newcomer->Description()));
		$party->setRace($race)->setOrigin($origin);

		$unit = new Unit();
		$id   = Lemuria::Catalog()->nextId(Domain::UNIT);
		$unit->setId($id);
		$unit->setSize(1)->setName('Einheit ' . $id)->setDescription('')->setRace($race);
		if ($this->newcomer->Inventory()->count()) {
			foreach ($this->newcomer->Inventory() as $item/* @var Quantity $item */) {
				$unit->Inventory()->add($item);
			}
		} else {
			foreach (self::EVERYONE as $class => $count) {
				$unit->Inventory()->add(new Quantity(self::createCommodity($class), $count));
			}
			foreach (self::INVENTORY[$race::class] as $class => $count) {
				$unit->Inventory()->add(new Quantity(self::createCommodity($class), $count));
			}
		}
		$this->addKnowledge($unit);

		$party->People()->add($unit);
		$origin->Residents()->add($unit);
		$party->Chronicle()->add($origin);
		$this->message(WelcomeMessage::class, $party)->p($party->Name());
	}

	private function cleanName(string $name): string {
		$name = str_replace(["\e", "\f", "\r", "\v"], '', $name);
		$name = str_replace(["\t", "\n"], ' ', $name);
		return Name::trimName($name);
	}

	private function cleanDescription(string $description): string {
		$description = str_replace(["\e", "\f", "\r", "\v"], '', $description);
		$description = str_replace(["\t", "\n"], ' ', $description);
		return Describe::trimDescription($description);
	}

	private function pickRace(): Race {
		if ($this->newcomer->Race()) {
			return $this->newcomer->Race();
		}
		$races = array_keys(self::LANDSCAPES);
		return self::createRace($races[array_rand($races)]);
	}

	private function pickOrigin(Race $race): Region {
		if ($this->newcomer->Origin()) {
			return $this->newcomer->Origin();
		}

		$locations  = new LocationPicker();
		$landscapes = self::LANDSCAPES[$race::class];
		if ($this->newcomer->Landscape()) {
			array_unshift($landscapes, $this->newcomer->Landscape()::class);
		}

		// First try: Consider only void regions.
		foreach ($landscapes as $type) {
			$locations->landscape(self::createLandscape($type));
			if ($race instanceof Aquan) {
				$locations->coastal();
			}
			if ($locations->void()->count()) {
				return $locations[0];
			}
			$locations->reset();
		}

		// Second try: Use the region with the least residents.
		foreach ($landscapes as $type) {
			$locations->landscape(self::createLandscape($type));
			if ($race instanceof Aquan) {
				$locations->coastal();
			}
			if ($locations->count()) {
				/** @var Region $region */
				/** @noinspection PhpUnnecessaryLocalVariableInspection */
				$region = $locations->Atlas()->sort(SortMode::BY_RESIDENTS)->current();
				return $region;
			}
			$locations->reset();
		}

		throw new LemuriaException('Origin region could not be picked.');
	}

	private function addKnowledge(Unit $unit): void {
		foreach (self::KNOWLEDGE[$unit->Race()::class] as $talent => $level) {
			$ability = new Ability(self::createTalent($talent), Ability::getExperience($level));
			$unit->Knowledge()->add($ability);
		}
	}
}
