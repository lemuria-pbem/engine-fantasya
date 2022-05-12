<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\getClass;
use function Lemuria\mbUcFirst;
use function Lemuria\undupChar;
use Lemuria\Engine\Fantasya\Combat\Spell\AbstractBattleSpell;
use Lemuria\Engine\Fantasya\Command\AbstractCommand;
use Lemuria\Engine\Fantasya\Command\Announcement;
use Lemuria\Engine\Fantasya\Command\Apply\AbstractApply;
use Lemuria\Engine\Fantasya\Command\Apply\HorseBlissBreed;
use Lemuria\Engine\Fantasya\Command\Attack;
use Lemuria\Engine\Fantasya\Command\Banner;
use Lemuria\Engine\Fantasya\Command\BattleSpell;
use Lemuria\Engine\Fantasya\Command\Buy;
use Lemuria\Engine\Fantasya\Command\Cast;
use Lemuria\Engine\Fantasya\Command\Cast\AbstractCast;
use Lemuria\Engine\Fantasya\Command\Comment;
use Lemuria\Engine\Fantasya\Command\Contact;
use Lemuria\Engine\Fantasya\Command\Create;
use Lemuria\Engine\Fantasya\Command\Create\Unicum;
use Lemuria\Engine\Fantasya\Command\Describe;
use Lemuria\Engine\Fantasya\Command\Destroy;
use Lemuria\Engine\Fantasya\Command\Destroy\Dismiss;
use Lemuria\Engine\Fantasya\Command\Destroy\Lose;
use Lemuria\Engine\Fantasya\Command\Devastate;
use Lemuria\Engine\Fantasya\Command\Disguise;
use Lemuria\Engine\Fantasya\Command\End;
use Lemuria\Engine\Fantasya\Command\Entertain;
use Lemuria\Engine\Fantasya\Command\Explore;
use Lemuria\Engine\Fantasya\Command\Fight;
use Lemuria\Engine\Fantasya\Command\Follow;
use Lemuria\Engine\Fantasya\Command\Gather;
use Lemuria\Engine\Fantasya\Command\Handover;
use Lemuria\Engine\Fantasya\Command\Handover\Grant;
use Lemuria\Engine\Fantasya\Command\Help;
use Lemuria\Engine\Fantasya\Command\Learn;
use Lemuria\Engine\Fantasya\Command\Loot;
use Lemuria\Engine\Fantasya\Command\Name;
use Lemuria\Engine\Fantasya\Command\Next;
use Lemuria\Engine\Fantasya\Command\NullCommand;
use Lemuria\Engine\Fantasya\Command\Number;
use Lemuria\Engine\Fantasya\Command\Operate\AbstractOperate;
use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Command\Origin;
use Lemuria\Engine\Fantasya\Command\Party;
use Lemuria\Engine\Fantasya\Command\Presetting;
use Lemuria\Engine\Fantasya\Command\Read;
use Lemuria\Engine\Fantasya\Command\Recruit;
use Lemuria\Engine\Fantasya\Command\Reserve;
use Lemuria\Engine\Fantasya\Command\Route;
use Lemuria\Engine\Fantasya\Command\Sell;
use Lemuria\Engine\Fantasya\Command\Sentinel;
use Lemuria\Engine\Fantasya\Command\Siege;
use Lemuria\Engine\Fantasya\Command\Sort;
use Lemuria\Engine\Fantasya\Command\Spy;
use Lemuria\Engine\Fantasya\Command\Steal;
use Lemuria\Engine\Fantasya\Command\Take;
use Lemuria\Engine\Fantasya\Command\Tax;
use Lemuria\Engine\Fantasya\Command\Teach;
use Lemuria\Engine\Fantasya\Command\Template;
use Lemuria\Engine\Fantasya\Command\Travel;
use Lemuria\Engine\Fantasya\Command\Trespass;
use Lemuria\Engine\Fantasya\Command\Trespass\Board;
use Lemuria\Engine\Fantasya\Command\Unit;
use Lemuria\Engine\Fantasya\Command\Use\Apply;
use Lemuria\Engine\Fantasya\Command\UseCommand;
use Lemuria\Engine\Fantasya\Command\Vacate;
use Lemuria\Engine\Fantasya\Command\Write;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Factory\Model\AnyBuilding;
use Lemuria\Engine\Fantasya\Factory\Model\AnyCastle;
use Lemuria\Engine\Fantasya\Factory\Model\AnyShip;
use Lemuria\Engine\Fantasya\Factory\Model\BattleSpellGrade;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Artifact;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\Acropolis;
use Lemuria\Model\Fantasya\Building\AlchemyKitchen;
use Lemuria\Model\Fantasya\Building\Blacksmith;
use Lemuria\Model\Fantasya\Building\Cabin;
use Lemuria\Model\Fantasya\Building\CamelBreeding;
use Lemuria\Model\Fantasya\Building\Canal;
use Lemuria\Model\Fantasya\Building\Citadel;
use Lemuria\Model\Fantasya\Building\College;
use Lemuria\Model\Fantasya\Building\Dockyard;
use Lemuria\Model\Fantasya\Building\Fort;
use Lemuria\Model\Fantasya\Building\GriffinBreeding;
use Lemuria\Model\Fantasya\Building\HorseBreeding;
use Lemuria\Model\Fantasya\Building\Lighthouse;
use Lemuria\Model\Fantasya\Building\Magespire;
use Lemuria\Model\Fantasya\Building\Megapolis;
use Lemuria\Model\Fantasya\Building\Mine;
use Lemuria\Model\Fantasya\Building\Palace;
use Lemuria\Model\Fantasya\Building\Pit;
use Lemuria\Model\Fantasya\Building\Port;
use Lemuria\Model\Fantasya\Building\Quarry;
use Lemuria\Model\Fantasya\Building\Quay;
use Lemuria\Model\Fantasya\Building\Saddlery;
use Lemuria\Model\Fantasya\Building\Sawmill;
use Lemuria\Model\Fantasya\Building\Shack;
use Lemuria\Model\Fantasya\Building\Signpost;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Building\Stronghold;
use Lemuria\Model\Fantasya\Building\Tavern;
use Lemuria\Model\Fantasya\Building\Tower;
use Lemuria\Model\Fantasya\Building\Workshop;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Carriage;
use Lemuria\Model\Fantasya\Commodity\CarriageWreck;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\ElephantArmor;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Griffinegg;
use Lemuria\Model\Fantasya\Commodity\Herb\Bubblemorel;
use Lemuria\Model\Fantasya\Commodity\Herb\Bugleweed;
use Lemuria\Model\Fantasya\Commodity\Herb\CaveLichen;
use Lemuria\Model\Fantasya\Commodity\Herb\CobaltFungus;
use Lemuria\Model\Fantasya\Commodity\Herb\Elvendear;
use Lemuria\Model\Fantasya\Commodity\Herb\FjordFungus;
use Lemuria\Model\Fantasya\Commodity\Herb\Flatroot;
use Lemuria\Model\Fantasya\Commodity\Herb\Gapgrowth;
use Lemuria\Model\Fantasya\Commodity\Herb\IceBegonia;
use Lemuria\Model\Fantasya\Commodity\Herb\Knotroot;
use Lemuria\Model\Fantasya\Commodity\Herb\Mandrake;
use Lemuria\Model\Fantasya\Commodity\Herb\Owlsgaze;
use Lemuria\Model\Fantasya\Commodity\Herb\Peyote;
use Lemuria\Model\Fantasya\Commodity\Herb\Rockweed;
use Lemuria\Model\Fantasya\Commodity\Herb\Sandreeker;
use Lemuria\Model\Fantasya\Commodity\Herb\Snowcrystal;
use Lemuria\Model\Fantasya\Commodity\Herb\SpiderIvy;
use Lemuria\Model\Fantasya\Commodity\Herb\TangyTemerity;
use Lemuria\Model\Fantasya\Commodity\Herb\Waterfinder;
use Lemuria\Model\Fantasya\Commodity\Herb\WhiteHemlock;
use Lemuria\Model\Fantasya\Commodity\Herb\Windbag;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Iron;
use Lemuria\Model\Fantasya\Commodity\Luxury\Balsam;
use Lemuria\Model\Fantasya\Commodity\Luxury\Fur;
use Lemuria\Model\Fantasya\Commodity\Luxury\Gem;
use Lemuria\Model\Fantasya\Commodity\Luxury\Myrrh;
use Lemuria\Model\Fantasya\Commodity\Luxury\Oil;
use Lemuria\Model\Fantasya\Commodity\Luxury\Olibanum;
use Lemuria\Model\Fantasya\Commodity\Luxury\Silk;
use Lemuria\Model\Fantasya\Commodity\Luxury\Spice;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Potion\BerserkBlood;
use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower;
use Lemuria\Model\Fantasya\Commodity\Potion\DrinkOfCreation;
use Lemuria\Model\Fantasya\Commodity\Potion\DrinkOfTruth;
use Lemuria\Model\Fantasya\Commodity\Potion\ElixirOfPower;
use Lemuria\Model\Fantasya\Commodity\Potion\GoliathWater;
use Lemuria\Model\Fantasya\Commodity\Potion\HealingPotion;
use Lemuria\Model\Fantasya\Commodity\Potion\HorseBliss;
use Lemuria\Model\Fantasya\Commodity\Potion\PeasantJoy;
use Lemuria\Model\Fantasya\Commodity\Potion\SevenLeagueTea;
use Lemuria\Model\Fantasya\Commodity\Potion\WaterOfLife;
use Lemuria\Model\Fantasya\Commodity\Potion\Woundshut;
use Lemuria\Model\Fantasya\Commodity\Protection\Armor;
use Lemuria\Model\Fantasya\Commodity\Protection\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Protection\LeatherArmor;
use Lemuria\Model\Fantasya\Commodity\Protection\Mail;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\DentedArmor;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\DentedIronshield;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\RustyMail;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\SplitWoodshield;
use Lemuria\Model\Fantasya\Commodity\Protection\Repairable\TatteredLeatherArmor;
use Lemuria\Model\Fantasya\Commodity\Protection\Woodshield;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Commodity\Stone;
use Lemuria\Model\Fantasya\Commodity\Trophy\Carnassial;
use Lemuria\Model\Fantasya\Commodity\Trophy\GoblinEar;
use Lemuria\Model\Fantasya\Commodity\Trophy\GriffinFeather;
use Lemuria\Model\Fantasya\Commodity\Trophy\Skull;
use Lemuria\Model\Fantasya\Commodity\Weapon\Battleaxe;
use Lemuria\Model\Fantasya\Commodity\Weapon\Bow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\Crossbow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\FounderingWarElephant;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\LooseWarhammer;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\RustyBattleaxe;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\RustySword;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\SkewedCatapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\StumpSpear;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\UngirtBow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Repairable\UngirtCrossbow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Spear;
use Lemuria\Model\Fantasya\Commodity\Weapon\Sword;
use Lemuria\Model\Fantasya\Commodity\Weapon\WarElephant;
use Lemuria\Model\Fantasya\Commodity\Weapon\Warhammer;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Composition\Scroll;
use Lemuria\Model\Fantasya\Composition\Spellbook;
use Lemuria\Model\Fantasya\Container;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Kind;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\RawMaterial;
use Lemuria\Model\Fantasya\Ship;
use Lemuria\Model\Fantasya\Ship\Boat;
use Lemuria\Model\Fantasya\Ship\Caravel;
use Lemuria\Model\Fantasya\Ship\Dragonship;
use Lemuria\Model\Fantasya\Ship\Galleon;
use Lemuria\Model\Fantasya\Ship\Longboat;
use Lemuria\Model\Fantasya\Ship\Trireme;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Spell\AstralChaos;
use Lemuria\Model\Fantasya\Spell\AstralPassage;
use Lemuria\Model\Fantasya\Spell\AuraTransfer;
use Lemuria\Model\Fantasya\Spell\CivilCommotion;
use Lemuria\Model\Fantasya\Spell\Daydream;
use Lemuria\Model\Fantasya\Spell\EagleEye;
use Lemuria\Model\Fantasya\Spell\Earthquake;
use Lemuria\Model\Fantasya\Spell\Farsight;
use Lemuria\Model\Fantasya\Spell\Fireball;
use Lemuria\Model\Fantasya\Spell\GazeOfTheGriffin;
use Lemuria\Model\Fantasya\Spell\GustOfWind;
use Lemuria\Model\Fantasya\Spell\InciteMonster;
use Lemuria\Model\Fantasya\Spell\Quacksalver;
use Lemuria\Model\Fantasya\Spell\Quickening;
use Lemuria\Model\Fantasya\Spell\RustyMist;
use Lemuria\Model\Fantasya\Spell\ShockWave;
use Lemuria\Model\Fantasya\Spell\SongOfPeace;
use Lemuria\Model\Fantasya\Spell\SoundlessShadow;
use Lemuria\Model\Fantasya\Spell\SummonEnts;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Alchemy;
use Lemuria\Model\Fantasya\Talent\Archery;
use Lemuria\Model\Fantasya\Talent\Armory;
use Lemuria\Model\Fantasya\Talent\Bladefighting;
use Lemuria\Model\Fantasya\Talent\Bowmaking;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Carriagemaking;
use Lemuria\Model\Fantasya\Talent\Catapulting;
use Lemuria\Model\Fantasya\Talent\Constructing;
use Lemuria\Model\Fantasya\Talent\Crossbowing;
use Lemuria\Model\Fantasya\Talent\Entertaining;
use Lemuria\Model\Fantasya\Talent\Espionage;
use Lemuria\Model\Fantasya\Talent\Fistfight;
use Lemuria\Model\Fantasya\Talent\Herballore;
use Lemuria\Model\Fantasya\Talent\Horsetaming;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Talent\Mining;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Talent\Quarrying;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Talent\Roadmaking;
use Lemuria\Model\Fantasya\Talent\Shipbuilding;
use Lemuria\Model\Fantasya\Talent\Spearfighting;
use Lemuria\Model\Fantasya\Talent\Stamina;
use Lemuria\Model\Fantasya\Talent\Tactics;
use Lemuria\Model\Fantasya\Talent\Taxcollecting;
use Lemuria\Model\Fantasya\Talent\Trading;
use Lemuria\Model\Fantasya\Talent\Weaponry;
use Lemuria\Model\Fantasya\Talent\Woodchopping;
use Lemuria\Model\Fantasya\Unicum as UnicumModel;
use Lemuria\Model\Fantasya\Unit as UnitModel;
use Lemuria\Model\World\Direction;
use Lemuria\Singleton;

/**
 * Parser helper class to find a command class.
 */
class CommandFactory
{
	use BuilderTrait;

	/**
	 * @var array(string=>mixed)
	 */
	protected array $verbs = [
		'//'           => 'KOMMENTAR',
		'@'            => 'VORLAGE',
		'ANGREIFEN'    => 'ATTACKIEREN',
		'ANGRIFF'      => 'ATTACKIEREN',
		'ATTACKE'      => 'ATTACKIEREN',
		'ATTACKIEREN'  => true,
		'BANNER'       => true,
		'BEKLAUEN'     => 'STEHLEN',
		'BELAGERE'     => 'BELAGERN',
		'BELAGERN'     => true,
		'BELAGERUNG'   => 'BELAGERN',
		'BENENNEN'     => 'NAME',
		'BENUTZEN'     => true,
		'BESCHREIBEN'  => 'BESCHREIBUNG',
		'BESCHREIBUNG' => true,
		'BESTEHLEN'    => 'STEHLEN',
		'BESTEIGEN'    => true,
		'BESTEUERN'    => 'TREIBEN',
		'BESTEUERUNG'  => 'TREIBEN',
		'BETRETEN'     => true,
		'BEUTE'        => true,
		'BEWACHEN'     => true,
		'BEWACHUNG'    => 'BEWACHEN',
		'BOTSCHAFT'    => true,
		'DEFAULT'      => 'VORLAGE',
		'DIEBSTAHL'    => 'STEHLEN',
		'EINHEIT'      => true,
		'EINTREIBEN'   => 'TREIBEN',
		'ENDE'         => true,
		'ENTLASSEN'    => true,
		'ERESSEA'      => 'PARTEI',
		'ERFORSCHEN'   => 'FORSCHEN',
		'ERSCHAFFEN'   => true,
		'FANTASYA'     => 'PARTEI',
		'FOLGEN'       => true,
		'FORSCHEN'     => true,
		'GIB'          => 'GEBEN',
		'GEBEN'        => true,
		'HELFEN'       => true,
		'HILFE'        => 'HELFEN',
		'ID'           => 'NUMMER',
		'KAMPF'        => 'KÄMPFEN',
		'KAMPFZAUBER'  => true,
		'KAEMPFEN'     => 'KÄMPFEN',
		'KAUFEN'       => true,
		'KÄMPFEN'      => true,
		'KOMMANDO'     => true,
		'KOMMENTAR'    => true,
		'KONTAKTIEREN' => true,
		'LEHREN'       => true,
		'LEHRER'       => 'LEHREN',
		'LEMURIA'      => 'PARTEI',
		'LERNEN'       => true,
		'LESEN'        => true,
		'LIES'         => 'LESEN',
		'LOCALE'       => true,
		'MACHEN'       => true,
		'NACH'         => 'REISEN',
		'NAME'         => true,
		'NÄCHSTER'     => true,
		'NAECHSTER'    => 'NÄCHSTER',
		'NEHMEN'       => true,
		'NIMM'         => 'NEHMEN',
		'NUMMER'       => true,
		'PARTEI'       => true,
		'REGION'       => true,
		'REISEN'       => true,
		'REKRUTEN'     => 'REKRUTIEREN',
		'REKRUTIEREN'  => true,
		'RESERVE'      => 'RESERVIEREN',
		'RESERVIEREN'  => true,
		'RESERVIERUNG' => 'RESERVIEREN',
		'ROUTE'        => true,
		'RUNDE'        => true,
		'SAMMELN'      => true,
		'SAMMLE'       => 'SAMMELN',
		'SCHREIBEN'    => true,
		'SORTIEREN'    => true,
		'SORTIERUNG'   => 'SORTIEREN',
		'SPIONAGE'     => 'SPIONIEREN',
		'SPIONIEREN'   => true,
		'STEHLEN'      => true,
		'TARNEN'       => true,
		'TARNUNG'      => 'TARNEN',
		'TAUSCHEN'     => 'SORTIEREN',
		'TEXT'         => 'BESCHREIBUNG',
		'TREIBEN'      => true,
		'UNTERHALTEN'  => true,
		'UNTERHALTUNG' => 'UNTERHALTEN',
		'UNTERSUCHEN'  => 'LESEN',
		'URSPRUNG'     => true,
		'ÜBERGEBEN'    => 'GIB',
		'UEBERGEBEN'   => 'GIB',
		'VERKAUFEN'    => true,
		'VERLASSEN'    => true,
		'VERLIEREN'    => true,
		'VERNICHTEN'   => true,
		'VORGABE'      => true,
		'VORLAGE'      => true,
		'ZAUBERE'      => 'ZAUBERN',
		'ZAUBERN'      => true,
		'ZERSTÖREN'    => true,
		'ZERSTOEREN'   => 'ZERSTÖREN'
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $buildings = [
		'Akademie'          => College::class,
		'Akropolis'         => Acropolis::class,
		'Alchemistenküche'  => AlchemyKitchen::class,
		'Baustelle'         => Site::class,
		'Befestigung'       => Fort::class,
		'Bergwerk'          => Mine::class,
		'Burg'              => AnyCastle::class,
		'Festung'           => Stronghold::class,
		'Gebäude'           => AnyBuilding::class,
		'Gebaeude'          => AnyBuilding::class,
		'Greifenzucht'      => GriffinBreeding::class,
		'Hafen'             => Port::class,
		'Holzfällerhütte'   => Cabin::class,
		'Holzfaellerhuette' => Cabin::class,
		'Kamelzucht'        => CamelBreeding::class,
		'Kanal'             => Canal::class,
		'Leuchtturm'        => Lighthouse::class,
		'Magierturm'        => Magespire::class,
		'Megapolis'         => Megapolis::class,
		'Mine'              => Pit::class,
		'Palast'            => Palace::class,
		'Pferdezucht'       => HorseBreeding::class,
		'Sägewerk'          => Sawmill::class,
		'Saegewerk'         => Sawmill::class,
		'Sattlerei'         => Saddlery::class,
		'Schiffswerft'      => Dockyard::class,
		'Schloss'           => Palace::class,
		'Schmiede'          => Blacksmith::class,
		'Steg'              => Quay::class,
		'Steinbruch'        => Quarry::class,
		'Steingrube'        => Shack::class,
		'Taverne'           => Tavern::class,
		'Turm'              => Tower::class,
		'Wegweiser'         => Signpost::class,
		'Werft'             => Dockyard::class,
		'Werkstatt'         => Workshop::class,
		'Zitadelle'         => Citadel::class
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $commodities = [
		'Alraunen'                   => Mandrake::class,
		'Armbrust'                   => Crossbow::class,
		'Armbrueste'                 => Crossbow::class,
		'Armbrüste'                  => Crossbow::class,
		'Balsame'                    => Balsam::class,
		'Bauernliebe'                => PeasantJoy::class,
		'Berserkerblute'             => BerserkBlood::class,
		'Blasenmorcheln'             => Bubblemorel::class,
		'Blaue baumringel'           => CobaltFungus::class,
		'Blauer baumringel'          => CobaltFungus::class,
		'Bogen'                      => Bow::class,
		'Boegen'                     => Bow::class,
		'Bögen'                      => Bow::class,
		'Eisblumen'                  => IceBegonia::class,
		'Eisen'                      => Iron::class,
		'Eisenschilde'               => Ironshield::class,
		'Elefant'                    => Elephant::class,
		'Elefanten'                  => Elephant::class,
		'Elefantenpanzer'            => ElephantArmor::class,
		'Elfenliebe'                 => Elvendear::class,
		'Elixier der macht'          => ElixirOfPower::class,
		'Elixiere der macht'         => ElixirOfPower::class,
		'Eulenaugen'                 => Owlsgaze::class,
		'Flachwurze'                 => Flatroot::class,
		'Fjordwuchse'                => FjordFungus::class,
		'Gehirnschmalze'             => Brainpower::class,
		'Gespaltene holzschilde'     => SplitWoodshield::class,
		'Gespaltener holzschild'     => SplitWoodshield::class,
		'Gewuerze'                   => Spice::class,
		'Gewürze'                    => Spice::class,
		'Goliathwaesser'             => GoliathWater::class,
		'Goliathwasser'              => GoliathWater::class,
		'Goliathwässer'              => GoliathWater::class,
		'Greif'                      => Griffin::class,
		'Greife'                     => Griffin::class,
		'Greifen'                    => Griffin::class,
		'Greifeneier'                => Griffinegg::class,
		'Greifenfedern'              => GriffinFeather::class,
		'Gruene spinneriche'         => SpiderIvy::class,
		'Gruener spinnerich'         => SpiderIvy::class,
		'Grüne spinneriche'          => SpiderIvy::class,
		'Grüner spinnerich'          => SpiderIvy::class,
		'Gurgelkraeuter'             => Bugleweed::class,
		'Gurgelkraute'               => Bugleweed::class,
		'Gurgelkräuter'              => Bugleweed::class,
		'Heiltrank'                  => HealingPotion::class,
		'Heiltränke'                 => HealingPotion::class,
		'Hoelzer'                    => Wood::class,
		'Holz'                       => Wood::class,
		'Holzschilde'                => Woodshield::class,
		'Hoehlenglimme'              => CaveLichen::class,
		'Höhlenglimme'               => CaveLichen::class,
		'Hölzer'                     => Wood::class,
		'Juwelen'                    => Gem::class,
		'Kakteenschwitze'            => Peyote::class,
		'Kamele'                     => Camel::class,
		'Katapulte'                  => Catapult::class,
		'Kettenhemden'               => Mail::class,
		'Knotige saugwurze'          => Knotroot::class,
		'Knotiger saugwurz'          => Knotroot::class,
		'Koboldohren'                => GoblinEar::class,
		'Kriegselefanten'            => WarElephant::class,
		'Kriegshammer'               => Warhammer::class,
		'Kriegshaemmer'              => Warhammer::class,
		'Kriegshämmer'               => Warhammer::class,
		'Lahme kriegselefanten'      => FounderingWarElephant::class,
		'Lahmer kriegselefant'       => FounderingWarElephant::class,
		'Lederruestungen'            => LeatherArmor::class,
		'Lederrüstungen'             => LeatherArmor::class,
		'Lockere kriegshaemmer'      => LooseWarhammer::class,
		'Lockere kriegshämmer'       => LooseWarhammer::class,
		'Lockerer kriegshammer'      => LooseWarhammer::class,
		'Marode katapulte'           => SkewedCatapult::class,
		'Marodes katapult'           => SkewedCatapult::class,
		'Myrrhen'                    => Myrrh::class,
		'Oele'                       => Oil::class,
		'Öle'                        => Oil::class,
		'Pegasi'                     => Pegasus::class,
		'Pegasus'                    => Pegasus::class,
		'Pelze'                      => Fur::class,
		'Pferd'                      => Horse::class,
		'Pferde'                     => Horse::class,
		'Pferdegluecke'              => HorseBliss::class,
		'Pferdeglücke'               => HorseBliss::class,
		'Plattenpanzer'              => Armor::class,
		'Reisszahn'                  => Carnassial::class,
		'Reisszaehne'                => Carnassial::class,
		'Reißzahn'                   => Carnassial::class,
		'Reißzähne'                  => Carnassial::class,
		'Rostige kettenhemden'       => RustyMail::class,
		'Rostige schwerter'          => RustySword::class,
		'Rostige streitaexte'        => RustyBattleaxe::class,
		'Rostige streitaxt'          => RustyBattleaxe::class,
		'Rostige streitäxte'         => RustyBattleaxe::class,
		'Rostiges kettenhemd'        => RustyMail::class,
		'Rostiges schwert'           => RustySword::class,
		'Sandfaeulen'                => Sandreeker::class,
		'Sandfäulen'                 => Sandreeker::class,
		'Schaffenstrunke'            => DrinkOfCreation::class,
		'Schaffenstrünke'            => DrinkOfCreation::class,
		'Schlaffe armbrust'          => UngirtCrossbow::class,
		'Schlaffe armbrueste'        => UngirtCrossbow::class,
		'Schlaffe armbrüste'         => UngirtCrossbow::class,
		'Schlaffe boegen'            => UngirtBow::class,
		'Schlaffe bögen'             => UngirtBow::class,
		'Schlaffer bogen'            => UngirtBow::class,
		'Schneekristalle'            => Snowcrystal::class,
		'Schwerter'                  => Sword::class,
		'Seiden'                     => Silk::class,
		'Siebenmeilentees'           => SevenLeagueTea::class,
		'Silber'                     => Silver::class,
		'Spaltwachse'                => Gapgrowth::class,
		'Speere'                     => Spear::class,
		'Stein'                      => Stone::class,
		'Steinbeisser'               => Rockweed::class,
		'Steinbeißer'                => Rockweed::class,
		'Steine'                     => Stone::class,
		'Streitaxt'                  => Battleaxe::class,
		'Streitaexte'                => Battleaxe::class,
		'Streitäxte'                 => Battleaxe::class,
		'Stumpfe speere'             => StumpSpear::class,
		'Stumpfer speer'             => StumpSpear::class,
		'Totenschaedel'              => Skull::class,
		'Totenschädel'               => Skull::class,
		'Traenke der wahrheit'       => DrinkOfTruth::class,
		'Tränke der wahrheit'        => DrinkOfTruth::class,
		'Trank der wahrheit'         => DrinkOfTruth::class,
		'Verbeulte eisenschilde'     => DentedIronshield::class,
		'Verbeulte ruestungen'       => DentedArmor::class,
		'Verbeulte rüstungen'        => DentedArmor::class,
		'Verbeultes eisenschild'     => DentedIronshield::class,
		'Wagen'                      => Carriage::class,
		'Wagenwracks'                => CarriageWreck::class,
		'Waesser des lebens'         => WaterOfLife::class,
		'Wasser des lebens'          => WaterOfLife::class,
		'Wässer des lebens'          => WaterOfLife::class,
		'Wasserfinder'               => Waterfinder::class,
		'Weihrauche'                 => Olibanum::class,
		'Weisse wueteriche'          => WhiteHemlock::class,
		'Weisser wueterich'          => WhiteHemlock::class,
		'Weiße wüteriche'            => WhiteHemlock::class,
		'Weißer wüterich'            => WhiteHemlock::class,
		'Windbeutel'                 => Windbag::class,
		'Wuerzige wagemute'          => TangyTemerity::class,
		'Wuerziger wagemut'          => TangyTemerity::class,
		'Wundsalben'                 => Woundshut::class,
		'Würzige wagemute'           => TangyTemerity::class,
		'Würziger wagemut'           => TangyTemerity::class,
		'Zerrissene lederrüstungen'  => TatteredLeatherArmor::class,
		'Zerrissene lederruestungen' => TatteredLeatherArmor::class
	];

	/**
	 * @var array(string=>Kind)
	 */
	protected array $kind = [
		'Kraeuter'    => Kind::Herb,
		'Kräuter'     => Kind::Herb,
		'Luxusgueter' => Kind::Luxury,
		'Luxusgüter'  => Kind::Luxury,
		'Luxuswaren'  => Kind::Luxury,
		'Ruestungen'  => Kind::Protection,
		'Rüstungen'   => Kind::Protection,
		'Schilde'     => Kind::Shield,
		'Tiere'       => Kind::Animal,
		'Traenke'     => Kind::Potion,
		'Tränke'      => Kind::Potion,
		'Transporter' => Kind::Transport,
		'Waffen'      => Kind::Weapon
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $compositions = [
		'Schriftrolle' => Scroll::class,
		'Zauberbuch'   => Spellbook::class,
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $spells = [
		'Adlerauge'           => EagleEye::class,
		'Astrales chaos'      => AstralChaos::class,
		'Astraler weg'        => AstralPassage::class,
		'Aufruhr verursachen' => CivilCommotion::class,
		'Auratransfer'        => AuraTransfer::class,
		'Beschleunigung'      => Quickening::class,
		'Blick des Greifen'   => GazeOfTheGriffin::class,
		'Erdbeben'            => Earthquake::class,
		'Erwecke baumhirten'  => SummonEnts::class,
		'Fernsicht'           => Farsight::class,
		'Feuerball'           => Fireball::class,
		'Friedenslied'        => SongOfPeace::class,
		'Lautloser schatten'  => SoundlessShadow::class,
		'Monster aufhetzen'   => InciteMonster::class,
		'Rosthauch'           => RustyMist::class,
		'Schockwelle'         => ShockWave::class,
		'Sturmboe'            => GustOfWind::class,
		'Sturmböe'            => GustOfWind::class,
		'Tagtraum'            => Daydream::class,
		'Wunderdoktor'        => Quacksalver::class
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $ships = [
		'Boot'          => Boat::class,
		'Drachenschiff' => Dragonship::class,
		'Galeone'       => Galleon::class,
		'Karavelle'     => Caravel::class,
		'Langboot'      => Longboat::class,
		'Schiff'        => AnyShip::class,
		'Trireme'       => Trireme::class
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $talents = [
		'Alchemie'          => Alchemy::class,
		'Armbrustschiessen' => Crossbowing::class,
		'Armbrustschießen'  => Crossbowing::class,
		'Ausdauer'          => Stamina::class,
		'Bergbau'           => Mining::class,
		'Bogenbau'          => Bowmaking::class,
		'Bogenschiessen'    => Archery::class,
		'Bogenschießen'     => Archery::class,
		'Burgenbau'         => Constructing::class,
		'Faustkampf'        => Fistfight::class,
		'Handeln'           => Trading::class,
		'Hiebwaffen'        => Bladefighting::class,
		'Holzfaellen'       => Woodchopping::class,
		'Holzfällen'        => Woodchopping::class,
		'Katapultbedienung' => Catapulting::class,
		'Katapultschiessen' => Catapulting::class,
		'Katapultschießen'  => Catapulting::class,
		'Kraeuterkunde'     => Herballore::class,
		'Kräuterkunde'      => Herballore::class,
		'Magie'             => Magic::class,
		'Navigation'        => Navigation::class,
		'Navigieren'        => Navigation::class,
		'Pferdedressur'     => Horsetaming::class,
		'Reiten'            => Riding::class,
		'Ruestungsbau'      => Armory::class,
		'Rüstungsbau'       => Armory::class,
		'Schiffbau'         => Shipbuilding::class,
		'Segeln'            => Navigation::class,
		'Speerkaempfen'     => Spearfighting::class,
		'Speerkämpfen'      => Spearfighting::class,
		'Speerkampf'        => Spearfighting::class,
		'Spionage'          => Espionage::class,
		'Spionieren'        => Espionage::class,
		'Stangenwaffen'     => Spearfighting::class,
		'Steinbau'          => Quarrying::class,
		'Steuereintreiben'  => Taxcollecting::class,
		'Steuereintreibung' => Taxcollecting::class,
		'Strassenbau'       => Roadmaking::class,
		'Straßenbau'        => Roadmaking::class,
		'Taktik'            => Tactics::class,
		'Tarnen'            => Camouflage::class,
		'Tarnung'           => Camouflage::class,
		'Unterhalten'       => Entertaining::class,
		'Unterhaltung'      => Entertaining::class,
		'Waffenbauen'       => Weaponry::class,
		'Wahrnehmen'        => Perception::class,
		'Wahrnehmung'       => Perception::class,
		'Wagenbau'          => Carriagemaking::class
	];

	protected array $domains = [
		'Burg'      => Domain::CONSTRUCTION,
		'Einheit'   => Domain::UNIT,
		'Kontinent' => Domain::CONTINENT,
		'Gebaeude'  => Domain::CONSTRUCTION,
		'Gebäude'   => Domain::CONSTRUCTION,
		'Partei'    => Domain::PARTY,
		'Region'    => Domain::LOCATION,
		'Schiff'    => Domain::VESSEL
	];

	protected array $directions = [
		'E'          => Direction::EAST,
		'N'          => Direction::NORTH,
		'NE'         => Direction::NORTHEAST,
		'NW'         => Direction::NORTHWEST,
		'S'          => Direction::SOUTH,
		'SE'         => Direction::SOUTHEAST,
		'SW'         => Direction::SOUTHWEST,
		'W'          => Direction::WEST,
		'East'       => Direction::EAST,
		'NO'         => Direction::NORTHEAST,
		'Norden'     => Direction::NORTH,
		'Nordosten'  => Direction::NORTHEAST,
		'Nordwesten' => Direction::NORTHWEST,
		'North'      => Direction::NORTH,
		'Northeast'  => Direction::NORTHEAST,
		'Northwest'  => Direction::NORTHWEST,
		'O'          => Direction::EAST,
		'Osten'      => Direction::EAST,
		'SO'         => Direction::SOUTHEAST,
		'South'      => Direction::SOUTH,
		'Southeast'  => Direction::SOUTHEAST,
		'Southwest'  => Direction::SOUTHWEST,
		'Süden'      => Direction::SOUTH,
		'Südosten'   => Direction::SOUTHEAST,
		'Südwesten'  => Direction::SOUTHWEST,
		'Westen'     => Direction::WEST
	];

	protected final const APPLY_NAMESPACE = 'Lemuria\\Engine\\Fantasya\\Command\\Apply\\';

	protected final const BATTLE_SPELL_NAMESPACE = 'Lemuria\\Engine\\Fantasya\\Combat\\Spell';

	protected final const CAST_NAMESPACE = 'Lemuria\\Engine\\Fantasya\\Command\\Cast\\';

	protected final const OPERATE_NAMESPACE = 'Lemuria\\Engine\\Fantasya\\Command\\Operate\\';

	protected final const APPLY_BREED = [
		HorseBliss::class => [HorseBreeding::class => HorseBlissBreed::class]
	];

	public function __construct(protected readonly Context $context) {
	}

	/**
	 * Create a Command.
	 *
	 * @throws UnknownCommandException
	 *
	 */
	public function create(Phrase $phrase): AbstractCommand {
		$verb = $this->identifyVerb($phrase->getVerb());
		try {
			$command = match ($verb) {
				'ATTACKIEREN'  => Attack::class,
				'BANNER'       => Banner::class,
				'BELAGERN'     => Siege::class,
				'BENUTZEN'     => UseCommand::class,
				'BESCHREIBUNG' => Describe::class,
				'BESTEIGEN'    => Board::class,
				'BETRETEN'     => Trespass::class,
				'BEUTE'        => Loot::class,
				'BEWACHEN'     => Sentinel::class,
				'BOTSCHAFT'    => Announcement::class,
				'EINHEIT'      => Unit::class,
				'ENDE'         => End::class,
				'ENTLASSEN'    => Dismiss::class,
				'ERSCHAFFEN'   => Unicum::class,
				'FOLGEN'       => Follow::class,
				'FORSCHEN'     => Explore::class,
				'GEBEN'        => Handover::class,
				'HELFEN'       => Help::class,
				'KAMPFZAUBER'  => BattleSpell::class,
				'KAUFEN'       => Buy::class,
				'KÄMPFEN'      => Fight::class,
				'KOMMANDO'     => Grant::class,
				'KOMMENTAR'    => Comment::class,
				'KONTAKTIEREN' => Contact::class,
				'LEHREN'       => Teach::class,
				'LERNEN'       => Learn::class,
				'LESEN'        => Read::class,
				'MACHEN'       => Create::class,
				'NAME'         => Name::class,
				'NÄCHSTER'     => Next::class,
				'NEHMEN'       => Take::class,
				'NUMMER'       => Number::class,
				'PARTEI'       => Party::class,
				'REISEN'       => Travel::class,
				'REKRUTIEREN'  => Recruit::class,
				'RESERVIEREN'  => Reserve::class,
				'ROUTE'        => Route::class,
				'SAMMELN'      => Gather::class,
				'SCHREIBEN'    => Write::class,
				'SORTIEREN'    => Sort::class,
				'SPIONIEREN'   => Spy::class,
				'STEHLEN'      => Steal::class,
				'TARNEN'       => Disguise::class,
				'TREIBEN'      => Tax::class,
				'UNTERHALTEN'  => Entertain::class,
				'URSPRUNG'     => Origin::class,
				'VERKAUFEN'    => Sell::class,
				'VERLASSEN'    => Vacate::class,
				'VERLIEREN'    => Lose::class,
				'VERNICHTEN'   => Devastate::class,
				'VORGABE'      => Presetting::class,
				'VORLAGE'      => Template::class,
				'ZAUBERN'      => Cast::class,
				'ZERSTÖREN'    => Destroy::class,

				'LOCALE', 'REGION', 'RUNDE' => NullCommand::class
			};
			return new $command($phrase, $this->context);
		} catch (\UnhandledMatchError) {
			throw new UnknownCommandException($phrase);
		}
	}

	public function isComposition(string $composition): bool {
		return is_string($this->getCandidate($composition, $this->compositions));
	}

	public function person(): Commodity {
		return self::createCommodity(Peasant::class);
	}

	public function domain(string $domain): Domain {
		$domain = ucfirst(mb_strtolower($domain));
		if (!isset($this->domains[$domain])) {
			throw new UnknownItemException($domain);
		}
		return $this->domains[$domain];
	}

	/**
	 * Create an artifact.
	 *
	 * @throws UnknownCommandException
	 */
	public function resource(string $artifact): Singleton {
		$commodity = $this->getCandidate($artifact, $this->commodities);
		if ($commodity) {
			$commodity = self::createCommodity($commodity);
			if ($commodity instanceof Artifact || $commodity instanceof RawMaterial) {
				return $commodity;
			}
			throw new UnknownItemException($commodity);
		}
		$building = $this->getCandidate($artifact, $this->buildings);
		if ($building) {
			return match ($building) {
				AnyBuilding::class, AnyCastle::class => new $building(),
				default                              => self::createBuilding($building)
			};
		}
		$ship = $this->getCandidate($artifact, $this->ships);
		if ($ship) {
			return match ($ship) {
				AnyShip::class => new $ship(),
				default        => self::createShip($ship)
			};
		}
		throw new UnknownItemException($artifact);
	}

	/**
	 * Create a Building.
	 *
	 * @throws UnknownCommandException
	 */
	public function building(string $building): Building {
		$buildingClass = $this->identifySingleton($building, $this->buildings);
		return self::createBuilding($buildingClass);
	}

	/**
	 * Create a Commodity.
	 *
	 * @throws UnknownCommandException
	 */
	public function commodity(string $commodity): Commodity {
		$commodityClass = $this->identifySingleton($commodity, $this->commodities);
		return self::createCommodity($commodityClass);
	}

	/**
	 * Create a commodity container for a given kind.
	 */
	public function kind(string $kind): ?Container {
		$kind = mbUcFirst(mb_strtolower($kind));
		if (isset($this->kind[$kind])) {
			return new Container($this->kind[$kind]);
		}
		return null;
	}

	/**
	 * Create a Composition.
	 *
	 * @throws UnknownCommandException
	 */
	public function composition(string $composition): Composition {
		$compositionClass = $this->identifySingleton($composition, $this->compositions);
		return self::createComposition($compositionClass);
	}

	/**
	 * Create a Spell.
	 *
	 * @throws UnknownCommandException
	 */
	public function spell(string $spell): Spell {
		$spellClass = $this->identifySingleton($spell, $this->spells);
		return self::createSpell($spellClass);
	}

	/**
	 * Check if a direction is route stop.
	 */
	public function isRouteStop(string $direction): bool {
		return str_starts_with('pause', strtolower($direction));
	}

	/**
	 * Validate a direction.
	 *
	 * @param string $direction
	 * @return string
	 * @throws UnknownCommandException
	 */
	public function direction(string $direction): Direction {
		if (strlen($direction) <= 2) {
			$direction = strtoupper($direction);
			$candidate = $this->directions[$direction] ?? null;
		} else {
			$candidate = $this->getCandidate($direction, $this->directions);
		}
		if ($candidate && Lemuria::World()->isDirection($candidate)) {
			return $candidate;
		}
		throw new UnknownItemException($direction);
	}

	/**
	 * Create a Ship.
	 *
	 * @throws UnknownCommandException
	 */
	public function ship(string $ship): Ship {
		$shipClass = $this->identifySingleton($ship, $this->ships);
		return self::createShip($shipClass);
	}

	/**
	 * Create a Talent.
	 *
	 * @throws UnknownCommandException
	 */
	public function talent(string $talent): Talent {
		$talentClass = $this->identifySingleton($talent, $this->talents);
		return self::createTalent($talentClass);
	}

	public function battleRow(string $position): BattleRow {
		return match(strtolower($position)) {
			'aggressiv'                   => BattleRow::AGGRESSIVE,
			'defensiv'                    => BattleRow::DEFENSIVE,
			'fliehe', 'fliehen', 'flucht' => BattleRow::REFUGEE,
			'hinten'                      => BattleRow::BACK,
			'nicht'                       => BattleRow::BYSTANDER,
			'', 'vorn', 'vorne'           => BattleRow::FRONT,
			'vorsichtig'                  => BattleRow::CAREFUL,
			default                       => throw new UnknownCommandException()
		};
	}

	public function applyPotion(Potion $potion, Apply $apply): AbstractApply {
		$potion = $this->getApplyPotion($potion, $apply->Unit());
		$class  = self::APPLY_NAMESPACE . $potion;
		if (class_exists($class)) {
			return new $class($apply);
		}
		throw new LemuriaException('Applying potion ' . $potion . ' is not implemented.');
	}

	public function castSpell(Spell $spell, Cast $cast): AbstractCast {
		$spell = getClass($spell);
		$class = self::CAST_NAMESPACE . $spell;
		if (class_exists($class)) {
			return new $class($cast);
		}
		throw new LemuriaException('Casting spell ' . $spell . ' is not implemented.');
	}

	public function castBattleSpell(BattleSpellGrade $grade): AbstractBattleSpell {
		$spell = getClass($grade->Spell());
		$class = self::BATTLE_SPELL_NAMESPACE . $spell;
		if (class_exists($class)) {
			return new $class($grade);
		}
		throw new LemuriaException('Casting battle spell ' . $spell . ' is not implemented.');
	}

	public function operateUnicum(UnicumModel $unicum, Operator $operator): AbstractOperate {
		$composition = getClass($unicum->Composition());
		$class       = self::OPERATE_NAMESPACE . $composition;
		if (class_exists($class)) {
			return new $class($this->context, $operator);
		}
		throw new LemuriaException('Operating composition ' . $composition . ' is not implemented.');
	}

	/**
	 * Match the command verb with a defined verb.
	 *
	 * @throws UnknownCommandException
	 */
	protected function identifyVerb(string $verb): string {
		$candidates = [];
		foreach ($this->verbs as $candidate => $isValid) {
			if (str_starts_with($candidate, $verb)) {
				if (is_string($isValid)) {
					$candidate = $isValid;
					$isValid   = $this->verbs[$isValid] ?? false;
				}
				if ($isValid === true) {
					$candidates[$candidate] = true;
				}
			}
		}
		if (count($candidates) === 1) {
			return key($candidates);
		}
		throw new UnknownCommandException($verb);
	}

	/**
	 * Match a Singleton.
	 */
	protected function identifySingleton(string $singleton, array $map): string {
		$candidate = $this->getCandidate($singleton, $map);
		if ($candidate) {
			return $candidate;
		}
		throw new UnknownItemException($singleton);
	}

	/**
	 * Parse a singleton.
	 */
	protected function getCandidate(string $singleton, array $map): ?string {
		$singleton  = str_replace(['-', '_', '~'], ' ', $singleton);
		$singleton  = mbUcFirst(mb_strtolower(undupChar(' ', $singleton)));
		$candidates = [];
		foreach ($map as $candidate => $singletonClass) {
			if (str_starts_with($candidate, $singleton)) {
				if ($candidate === $singleton) {
					return $singletonClass;
				}
				$candidates[] = $singletonClass;
			}
		}
		return count($candidates) === 1 ? $candidates[0] : null;
	}

	protected function getApplyPotion(Potion $potion, UnitModel $unit): string {
		$breed = self::APPLY_BREED[$potion::class] ?? null;
		if ($breed) {
			$building = self::createBuilding(key($breed));
			if ($unit->Construction()?->Building() === $building) {
				return getClass(current($breed));
			}
		}
		return getClass($potion);
	}
}
