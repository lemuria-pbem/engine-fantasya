<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\AbstractCommand;
use Lemuria\Engine\Fantasya\Command\Announcement;
use Lemuria\Engine\Fantasya\Command\Banner;
use Lemuria\Engine\Fantasya\Command\Buy;
use Lemuria\Engine\Fantasya\Command\Comment;
use Lemuria\Engine\Fantasya\Command\Contact;
use Lemuria\Engine\Fantasya\Command\Create;
use Lemuria\Engine\Fantasya\Command\DefaultCommand;
use Lemuria\Engine\Fantasya\Command\Describe;
use Lemuria\Engine\Fantasya\Command\Destroy;
use Lemuria\Engine\Fantasya\Command\Destroy\Dismiss;
use Lemuria\Engine\Fantasya\Command\Destroy\Lose;
use Lemuria\Engine\Fantasya\Command\Disguise;
use Lemuria\Engine\Fantasya\Command\End;
use Lemuria\Engine\Fantasya\Command\Entertain;
use Lemuria\Engine\Fantasya\Command\Fight;
use Lemuria\Engine\Fantasya\Command\Handover;
use Lemuria\Engine\Fantasya\Command\Handover\Grant;
use Lemuria\Engine\Fantasya\Command\Help;
use Lemuria\Engine\Fantasya\Command\Learn;
use Lemuria\Engine\Fantasya\Command\Leave;
use Lemuria\Engine\Fantasya\Command\Name;
use Lemuria\Engine\Fantasya\Command\Next;
use Lemuria\Engine\Fantasya\Command\NullCommand;
use Lemuria\Engine\Fantasya\Command\Number;
use Lemuria\Engine\Fantasya\Command\Origin;
use Lemuria\Engine\Fantasya\Command\Party;
use Lemuria\Engine\Fantasya\Command\Recruit;
use Lemuria\Engine\Fantasya\Command\Reserve;
use Lemuria\Engine\Fantasya\Command\Sell;
use Lemuria\Engine\Fantasya\Command\Sentinel;
use Lemuria\Engine\Fantasya\Command\Sort;
use Lemuria\Engine\Fantasya\Command\Tax;
use Lemuria\Engine\Fantasya\Command\Teach;
use Lemuria\Engine\Fantasya\Command\Travel;
use Lemuria\Engine\Fantasya\Command\Trespass;
use Lemuria\Engine\Fantasya\Command\Trespass\Board;
use Lemuria\Engine\Fantasya\Command\Unit;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Artifact;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\Cabin;
use Lemuria\Model\Fantasya\Building\Citadel;
use Lemuria\Model\Fantasya\Building\Fort;
use Lemuria\Model\Fantasya\Building\Palace;
use Lemuria\Model\Fantasya\Building\Sawmill;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Building\Stronghold;
use Lemuria\Model\Fantasya\Building\Tower;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Armor;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Carriage;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Griffinegg;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Iron;
use Lemuria\Model\Fantasya\Commodity\Ironshield;
use Lemuria\Model\Fantasya\Commodity\Luxury\Balsam;
use Lemuria\Model\Fantasya\Commodity\Luxury\Fur;
use Lemuria\Model\Fantasya\Commodity\Luxury\Gem;
use Lemuria\Model\Fantasya\Commodity\Luxury\Myrrh;
use Lemuria\Model\Fantasya\Commodity\Luxury\Oil;
use Lemuria\Model\Fantasya\Commodity\Luxury\Olibanum;
use Lemuria\Model\Fantasya\Commodity\Luxury\Silk;
use Lemuria\Model\Fantasya\Commodity\Luxury\Spice;
use Lemuria\Model\Fantasya\Commodity\Mail;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Commodity\Stone;
use Lemuria\Model\Fantasya\Commodity\Weapon\Battleaxe;
use Lemuria\Model\Fantasya\Commodity\Weapon\Bow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\Crossbow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Spear;
use Lemuria\Model\Fantasya\Commodity\Weapon\Sword;
use Lemuria\Model\Fantasya\Commodity\Weapon\Warhammer;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Commodity\Woodshield;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\RawMaterial;
use Lemuria\Model\Fantasya\Ship;
use Lemuria\Model\Fantasya\Ship\Boat;
use Lemuria\Model\Fantasya\Ship\Caravel;
use Lemuria\Model\Fantasya\Ship\Dragonship;
use Lemuria\Model\Fantasya\Ship\Galleon;
use Lemuria\Model\Fantasya\Ship\Longboat;
use Lemuria\Model\Fantasya\Ship\Trireme;
use Lemuria\Model\Fantasya\Talent;
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
use Lemuria\Model\World;
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
		'BANNER'       => true,
		'BENENNEN'     => 'NAME',
		'BESCHREIBEN'  => 'BESCHREIBUNG',
		'BESCHREIBUNG' => true,
		'BESTEIGEN'    => true,
		'BESTEUERN'    => 'TREIBEN',
		'BESTEUERUNG'  => 'TREIBEN',
		'BETRETEN'     => true,
		'BEWACHEN'     => true,
		'BEWACHUNG'    => 'BEWACHEN',
		'BOTSCHAFT'    => true,
		'DEFAULT'      => 'VORLAGE',
		'EINHEIT'      => true,
		'EINTREIBEN'   => 'TREIBEN',
		'ENDE'         => true,
		'ENTLASSEN'    => true,
		'ERESSEA'      => 'PARTEI',
		'FANTASYA'     => 'PARTEI',
		'GIB'          => true,
		'GEBEN'        => 'GIB',
		'HELFEN'       => true,
		'HILFE'        => 'HELFEN',
		'ID'           => 'NUMMER',
		'KAMPF'        => 'KÄMPFEN',
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
		'LOCALE'       => true,
		'MACHEN'       => true,
		'NACH'         => 'REISEN',
		'NAME'         => true,
		'NÄCHSTER'     => true,
		'NAECHSTER'    => 'NÄCHSTER',
		'NUMMER'       => true,
		'PARTEI'       => true,
		'REGION'       => true,
		'REISEN'       => true,
		'REKRUTEN'     => 'REKRUTIEREN',
		'REKRUTIEREN'  => true,
		'RESERVE'      => 'RESERVIEREN',
		'RESERVIEREN'  => true,
		'RESERVIERUNG' => 'RESERVIEREN',
		'RUNDE'        => true,
		'SORTIEREN'    => true,
		'SORTIERUNG'   => 'SORTIEREN',
		'TARNEN'       => true,
		'TARNUNG'      => 'TARNEN',
		'TAUSCHEN'     => 'SORTIEREN',
		'TEXT'         => 'BESCHREIBUNG',
		'TREIBEN'      => true,
		'UNTERHALTEN'  => true,
		'UNTERHALTUNG' => 'UNTERHALTEN',
		'URSPRUNG'     => true,
		'ÜBERGEBEN'    => 'GIB',
		'UEBERGEBEN'   => 'GIB',
		'VERKAUFEN'    => true,
		'VERLASSEN'    => true,
		'VERLIEREN'    => true,
		'VORLAGE'      => true,
		'ZERSTÖREN'    => true,
		'ZERSTOEREN'   => 'ZERSTÖREN'
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $buildings = [
		'Baustelle' => Site::class,
		'Befestigung' => Fort::class,
		'Burg' => Site::class,
		'Festung' => Stronghold::class,
		'Gebäude' => Site::class,
		'Gebaeude' => Site::class,
		'Holzfällerhütte' => Cabin::class,
		'Palast' => Palace::class,
		'Sägewerk' => Sawmill::class,
		'Turm' => Tower::class,
		'Zitadelle' => Citadel::class
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $commodities = [
		'Armbrust'      => Crossbow::class,
		'Armbrueste'    => Crossbow::class,
		'Armbrüste'     => Crossbow::class,
		'Balsame'       => Balsam::class,
		'Bogen'         => Bow::class,
		'Boegen'        => Bow::class,
		'Bögen'         => Bow::class,
		'Eisen'         => Iron::class,
		'Eisenschilde'  => Ironshield::class,
		'Elefanten'     => Elephant::class,
		'Gewuerze'      => Spice::class,
		'Gewürze'       => Spice::class,
		'Greife'        => Griffin::class,
		'Greifeneier'   => Griffinegg::class,
		'Hoelzer'       => Wood::class,
		'Holz'          => Wood::class,
		'Holzschilde'   => Woodshield::class,
		'Hölzer'        => Wood::class,
		'Juwelen'       => Gem::class,
		'Kamele'        => Camel::class,
		'Katapulte'     => Catapult::class,
		'Kettenhemden'  => Mail::class,
		'Kriegshammer'  => Warhammer::class,
		'Kriegshaemmer' => Warhammer::class,
		'Kriegshämmer'  => Warhammer::class,
		'Myrrhen'       => Myrrh::class,
		'Oele'          => Oil::class,
		'Öle'           => Oil::class,
		'Pelze'         => Fur::class,
		'Pferde'        => Horse::class,
		'Plattenpanzer' => Armor::class,
		'Schwerter'     => Sword::class,
		'Seiden'        => Silk::class,
		'Silber'        => Silver::class,
		'Speere'        => Spear::class,
		'Steine'        => Stone::class,
		'Streitaxt'     => Battleaxe::class,
		'Streitaexte'   => Battleaxe::class,
		'Streitäxte'    => Battleaxe::class,
		'Wagen'         => Carriage::class,
		'Weihrauch'     => Olibanum::class
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
		'Schiff'        => Boat::class,
		'Trireme'       => Trireme::class
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $talents = [
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

	protected array $directions = [
		World::EAST      => World::EAST,
		World::NORTH     => World::NORTH,
		World::NORTHEAST => World::NORTHEAST,
		World::NORTHWEST => World::NORTHWEST,
		World::SOUTH     => World::SOUTH,
		World::SOUTHEAST => World::SOUTHEAST,
		World::SOUTHWEST => World::SOUTHWEST,
		World::WEST      => World::WEST,
		'East'           => World::EAST,
		'NO'             => World::NORTHEAST,
		'Norden'         => World::NORTH,
		'Nordosten'      => World::NORTHEAST,
		'Nordwesten'     => World::NORTHWEST,
		'North'          => World::NORTH,
		'Northeast'      => World::NORTHEAST,
		'Northwest'      => World::NORTHWEST,
		'O'              => World::EAST,
		'Osten'          => World::EAST,
		'SO'             => World::SOUTHEAST,
		'South'          => World::SOUTH,
		'Southeast'      => World::SOUTHEAST,
		'Southwest'      => World::SOUTHWEST,
		'Süden'          => World::SOUTH,
		'Südosten'       => World::SOUTHEAST,
		'Südwesten'      => World::SOUTHWEST,
		'Westen'         => World::WEST
	];

	public function __construct(protected Context $context) {
	}

	/**
	 * Create a Command.
	 *
	 * @throws UnknownCommandException
	 */
	public function create(Phrase $phrase): AbstractCommand {
		$verb = $this->identifyVerb($phrase->getVerb());
		try {
			$command = match ($verb) {
				'BANNER'       => Banner::class,
				'BESCHREIBUNG' => Describe::class,
				'BESTEIGEN'    => Board::class,
				'BETRETEN'     => Trespass::class,
				'BEWACHEN'     => Sentinel::class,
				'BOTSCHAFT'    => Announcement::class,
				'EINHEIT'      => Unit::class,
				'ENDE'         => End::class,
				'ENTLASSEN'    => Dismiss::class,
				'GIB'          => Handover::class,
				'HELFEN'       => Help::class,
				'KAUFEN'       => Buy::class,
				'KÄMPFEN'      => Fight::class,
				'KOMMANDO'     => Grant::class,
				'KOMMENTAR'    => Comment::class,
				'KONTAKTIEREN' => Contact::class,
				'LEHREN'       => Teach::class,
				'LERNEN'       => Learn::class,
				'LOCALE'       => NullCommand::class,
				'MACHEN'       => Create::class,
				'NAME'         => Name::class,
				'NÄCHSTER'     => Next::class,
				'NUMMER'       => Number::class,
				'PARTEI'       => Party::class,
				'REGION'       => NullCommand::class,
				'REISEN'       => Travel::class,
				'REKRUTIEREN'  => Recruit::class,
				'RESERVIEREN'  => Reserve::class,
				'RUNDE'        => NullCommand::class,
				'SORTIEREN'    => Sort::class,
				'TARNEN'       => Disguise::class,
				'TREIBEN'      => Tax::class,
				'UNTERHALTEN'  => Entertain::class,
				'URSPRUNG'     => Origin::class,
				'VERKAUFEN'    => Sell::class,
				'VERLASSEN'    => Leave::class,
				'VERLIEREN'    => Lose::class,
				'VORLAGE'      => DefaultCommand::class,
				'ZERSTÖREN'    => Destroy::class
			};
			return new $command($phrase, $this->context);
		} catch (\UnhandledMatchError) {
			throw new UnknownCommandException($phrase);
		}
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
			return self::createBuilding($building);
		}
		$ship = $this->getCandidate($artifact, $this->ships);
		if ($ship) {
			return self::createShip($ship);
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
	 * Validate a direction.
	 *
	 * @param string $direction
	 * @return string
	 * @throws UnknownCommandException
	 */
	public function direction(string $direction): string {
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
			reset($candidates);
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
		$singleton  = ucfirst(strtolower($singleton));
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
}
