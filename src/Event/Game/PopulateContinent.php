<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use function Lemuria\randArray;
use function Lemuria\randElement;
use function Lemuria\randFloat;
use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Monster\Bear;
use Lemuria\Model\Fantasya\Commodity\Monster\Ent;
use Lemuria\Model\Fantasya\Commodity\Monster\Ghoul;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Kraken;
use Lemuria\Model\Fantasya\Commodity\Monster\Skeleton;
use Lemuria\Model\Fantasya\Commodity\Monster\Wolf;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\DentedArmor;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\DentedIronshield;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\RustyMail;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\SplitWoodshield;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\TatteredLeatherArmor;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\LooseWarhammer;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\RustyBattleaxe;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\RustySword;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\StumpSpear;
use Lemuria\Model\Fantasya\Continent;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Highland;
use Lemuria\Model\Fantasya\Landscape\Mountain;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Landscape\Swamp;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Quantity;

/**
 * This event populates a whole continent with monsters.
 */
final class PopulateContinent extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const CONTINENT = 'continent';

	public final const CHANCES = 'chances';

	public final const SIZES = 'sizes';

	public final const VARIATION = 'variation';

	private const LANDSCAPE = [
		Bear::class     => Forest::class,
		Ent::class      => Forest::class,
		Ghoul::class    => Swamp::class,
		Goblin::class   => Plain::class,
		Skeleton::class => Mountain::class,
		Kraken::class   => Ocean::class,
		Wolf::class     => Forest::class,
		Zombie::class   => Highland::class
	];

	private const SIZE = [
		Bear::class     =>  1,
		Ent::class      =>  4,
		Ghoul::class    =>  8,
		Goblin::class   => 10,
		Skeleton::class =>  8,
		Kraken::class   =>  1,
		Wolf::class     =>  7,
		Zombie::class   =>  6
	];

	private const CHANCE = [
		Bear::class     =>  7,
		Ent::class      => 25,
		Ghoul::class    => 30,
		Goblin::class   => 15,
		Skeleton::class => 50,
		Kraken::class   => 25,
		Wolf::class     => 10,
		Zombie::class   => 40
	];

	private const HAS_SHIELD = 0.5;

	private const HAS_ARMOR = 0.35;

	private const WEAPONS = [LooseWarhammer::class, RustyBattleaxe::class, RustySword::class, StumpSpear::class];

	private const ARMOR = [DentedArmor::class, RustyMail::class, TatteredLeatherArmor::class];

	private const SHIELD = [DentedIronshield::class, SplitWoodshield::class];

	private const MAX_SKILL = 5;

	private const VARIATION_VALUE = 0.33;

	/**
	 * @var array<Create>
	 */
	private array $creates = [];

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function setOptions(array $options): PopulateContinent {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$monsters  = Party::get(Spawn::getPartyId(Type::Monster));
		$zombies   = Party::get(Id::fromId(Spawn::ZOMBIES));
		$continent = Continent::get($this->getIdOption(self::CONTINENT));
		$chances   = $this->hasOption(self::CHANCES) ? $this->getOption(self::CHANCES, 'array') : self::CHANCE;
		$sizes     = $this->hasOption(self::SIZES) ? $this->getOption(self::SIZES, 'array') : self::SIZES;
		$variation = $this->hasOption(self::VARIATION) ? $this->getOption(self::VARIATION, 'number') : self::VARIATION_VALUE;
		foreach ($chances as $race => $chance) {
			$party       = $race === Zombie::class ? $zombies : $monsters;
			$regions     = [];
			$monster     = self::createMonster($race);
			$environment = self::createLandscape(self::LANDSCAPE[$race]);
			$raceSize    = $sizes[$race] ?? self::SIZE[$race];
			foreach ($continent->Landmass() as $region) {
				if ($region->Landscape() === $environment) {
					$regions[] = $region;
				}
			}
			$count = (int)ceil(count($regions) / $chance);
			foreach (randArray($regions, $count) as $region) {
				$size            = (int)round((1.0 + $variation * 2.0 * (randFloat() - 0.5)) * $raceSize);
				$create          = new Create($party, $region);
				$this->creates[] = $create->add(new Gang($monster, $size));
			}
		}
	}

	protected function run(): void {
		foreach ($this->creates as $create) {
			foreach ($create->act()->getUnits() as $unit) {
				if ($unit->Race() instanceof Skeleton) {
					$size      = $unit->Size();
					$inventory = $unit->Inventory();
					$weapon    = self::createWeapon(randElement(self::WEAPONS));
					$skill     = $weapon->getSkill();
					/** @var Commodity $weapon */
					$inventory->add(new Quantity($weapon, $size));
					$experience = $skill->Experience() + Ability::getExperience(randInt(0, self::MAX_SKILL));
					$unit->Knowledge()->add(new Ability($skill->Talent(), $experience));

					$chance = randFloat();
					if ($chance <= self::HAS_SHIELD) {
						$shield = randElement(self::SHIELD);
						$inventory->add(new Quantity(self::createCommodity($shield), $size));
					} elseif ($chance <= self::HAS_SHIELD + self::HAS_ARMOR) {
						$armor = randElement(self::ARMOR);
						$inventory->add(new Quantity(self::createCommodity($armor), $size));
					} else {
						$armor  = randElement(self::ARMOR);
						$inventory->add(new Quantity(self::createCommodity($armor), $size));
						$shield = randElement(self::SHIELD);
						$inventory->add(new Quantity(self::createCommodity($shield), $size));
					}
				}
			}
		}
	}
}
