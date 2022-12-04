<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\Behaviour\Monster\Ghoul;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\Namer;
use Lemuria\Engine\Fantasya\Factory\Namer\RaceNamer;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\CreateMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity\Monster\AirElemental;
use Lemuria\Model\Fantasya\Commodity\Monster\Bear;
use Lemuria\Model\Fantasya\Commodity\Monster\EarthElemental;
use Lemuria\Model\Fantasya\Commodity\Monster\FireElemental;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Skeleton;
use Lemuria\Model\Fantasya\Commodity\Monster\WaterElemental;
use Lemuria\Model\Fantasya\Commodity\Monster\Wolf;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

/**
 * A monster event that generates a new unit.
 */
class Create implements Act
{
	use BuilderTrait;
	use MessageTrait;
	use OptionsTrait;

	protected const NAMER = [
		'' => RaceNamer::class
	];

	protected const BATTLE_ROW = [
		''                    => BattleRow::FRONT,
		AirElemental::class   => BattleRow::AGGRESSIVE,
		EarthElemental::class => BattleRow::AGGRESSIVE,
		FireElemental::class  => BattleRow::AGGRESSIVE,
		Goblin::class         => BattleRow::CAREFUL,
		Skeleton::class       => BattleRow::AGGRESSIVE,
		WaterElemental::class => BattleRow::AGGRESSIVE,
		Zombie::class         => BattleRow::AGGRESSIVE
	];

	protected const IS_SENSING = [
		''            => 0,
		Bear::class   => 7,
		Ghoul::class  => 1,
		Wolf::class   => 4,
		Zombie::class => 1
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

	private Talent $perception;

	private Talent $camouflage;

	public function __construct(protected Party $party, protected Region $region) {
		$this->perception = self::createTalent(Perception::class);
		$this->camouflage = self::createTalent(Camouflage::class);
	}

	public function act(): Create {
		foreach ($this->gangs as $gang) {
			$unit = new Unit();
			$unit->setId(Lemuria::Catalog()->nextId(Domain::UNIT));
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
			$perception = self::IS_SENSING[$race] ?? self::IS_SENSING[''];
			if ($perception > 0) {
				$unit->Knowledge()->add(new Ability($this->perception, Ability::getExperience($perception)));
			}
			$camouflage = self::IS_HIDING[$race] ?? self::IS_HIDING[''];
			if ($camouflage > 0) {
				$unit->Knowledge()->add(new Ability($this->camouflage, Ability::getExperience($camouflage)));
				$unit->setIsHiding(true);
			}
			$this->units[] = $unit;
			$this->message(CreateMessage::class, $unit);
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
