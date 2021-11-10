<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Factory\Namer;
use Lemuria\Engine\Fantasya\Factory\Namer\RaceNamer;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Combat;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Skeleton;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Unit;

/**
 * A monster event that generates a new unit.
 */
class Create implements Act
{
	use BuilderTrait;
	use OptionsTrait;

	protected const NAMER = [
		'' => RaceNamer::class
	];

	protected const BATTLE_ROW = [
		''              => Combat::FRONT,
		Goblin::class   => Combat::CAREFUL,
		Skeleton::class => Combat::AGGRESSIVE,
		Zombie::class   => Combat::AGGRESSIVE
	];

	protected const IS_HIDING = [
		''            => 0,
		Goblin::class => 5
	];

	/**
	 * @var Gang[]
	 */
	protected array $gangs = [];

	/**
	 * @var Unit[]
	 */
	protected array $units = [];

	private Talent $camouflage;

	public function __construct(protected Party $party, protected Region $region) {
		$this->camouflage = self::createTalent(Camouflage::class);
	}

	public function act(): Create {
		foreach ($this->gangs as $gang) {
			$unit = new Unit();
			$unit->setId(Lemuria::Catalog()->nextId(Catalog::UNITS));
			$unit->setRace($gang->Race())->setSize($gang->Count());
			$this->party->People()->add($unit);
			$this->region->Residents()->add($unit);
			$this->party->Chronicle()->add($this->region);

			$race = $gang->Race()::class;

			$namerClass = self::NAMER[$race] ?? self::NAMER[''];
			/** @var Namer $namer */
			$namer = new $namerClass();
			$namer->name($unit);

			$battleRow = self::BATTLE_ROW[$race] ?? self::BATTLE_ROW[''];
			$unit->setBattleRow($battleRow);
			$camouflage = self::IS_HIDING[$race] ?? self::IS_HIDING[''];
			if ($camouflage > 0) {
				$unit->Knowledge()->add(new Ability($this->camouflage, Ability::getExperience($camouflage)));
				$unit->setIsHiding(true);
			}
			$this->units[] = $unit;
			Lemuria::Log()->debug('A new unit of ' . $gang . ' has been spawned in ' . $this->region . '.');
		}
		return $this;
	}

	public function add(Gang $gang): Create {
		$this->gangs[] = $gang;
		return $this;
	}

	public function getUnits(): array {
		return $this->units;
	}
}
