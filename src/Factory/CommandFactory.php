<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Factory;

use Lemuria\Engine\Lemuria\Command\AbstractCommand;
use Lemuria\Engine\Lemuria\Command\Board;
use Lemuria\Engine\Lemuria\Command\Comment;
use Lemuria\Engine\Lemuria\Command\Contact;
use Lemuria\Engine\Lemuria\Command\Create;
use Lemuria\Engine\Lemuria\Command\DefaultCommand;
use Lemuria\Engine\Lemuria\Command\Describe;
use Lemuria\Engine\Lemuria\Command\Disguise;
use Lemuria\Engine\Lemuria\Command\End;
use Lemuria\Engine\Lemuria\Command\Enter;
use Lemuria\Engine\Lemuria\Command\Entertain;
use Lemuria\Engine\Lemuria\Command\Fight;
use Lemuria\Engine\Lemuria\Command\Handover;
use Lemuria\Engine\Lemuria\Command\Handover\Grant;
use Lemuria\Engine\Lemuria\Command\Help;
use Lemuria\Engine\Lemuria\Command\Learn;
use Lemuria\Engine\Lemuria\Command\Leave;
use Lemuria\Engine\Lemuria\Command\Name;
use Lemuria\Engine\Lemuria\Command\Next;
use Lemuria\Engine\Lemuria\Command\Number;
use Lemuria\Engine\Lemuria\Command\Origin;
use Lemuria\Engine\Lemuria\Command\Party;
use Lemuria\Engine\Lemuria\Command\Recruit;
use Lemuria\Engine\Lemuria\Command\Reserve;
use Lemuria\Engine\Lemuria\Command\Sentinel;
use Lemuria\Engine\Lemuria\Command\Sort;
use Lemuria\Engine\Lemuria\Command\Tax;
use Lemuria\Engine\Lemuria\Command\Teach;
use Lemuria\Engine\Lemuria\Command\Unit;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Commodity\Armor;
use Lemuria\Model\Lemuria\Commodity\Camel;
use Lemuria\Model\Lemuria\Commodity\Carriage;
use Lemuria\Model\Lemuria\Commodity\Elephant;
use Lemuria\Model\Lemuria\Commodity\Granite;
use Lemuria\Model\Lemuria\Commodity\Griffin;
use Lemuria\Model\Lemuria\Commodity\Griffinegg;
use Lemuria\Model\Lemuria\Commodity\Horse;
use Lemuria\Model\Lemuria\Commodity\Iron;
use Lemuria\Model\Lemuria\Commodity\Ironshield;
use Lemuria\Model\Lemuria\Commodity\Luxury\Balsam;
use Lemuria\Model\Lemuria\Commodity\Luxury\Fur;
use Lemuria\Model\Lemuria\Commodity\Luxury\Gem;
use Lemuria\Model\Lemuria\Commodity\Luxury\Myrrh;
use Lemuria\Model\Lemuria\Commodity\Luxury\Oil;
use Lemuria\Model\Lemuria\Commodity\Luxury\Olibanum;
use Lemuria\Model\Lemuria\Commodity\Luxury\Silk;
use Lemuria\Model\Lemuria\Commodity\Luxury\Spice;
use Lemuria\Model\Lemuria\Commodity\Mail;
use Lemuria\Model\Lemuria\Commodity\Ore;
use Lemuria\Model\Lemuria\Commodity\Silver;
use Lemuria\Model\Lemuria\Commodity\Stone;
use Lemuria\Model\Lemuria\Commodity\Tree;
use Lemuria\Model\Lemuria\Commodity\Weapon\Battleaxe;
use Lemuria\Model\Lemuria\Commodity\Weapon\Bow;
use Lemuria\Model\Lemuria\Commodity\Weapon\Catapult;
use Lemuria\Model\Lemuria\Commodity\Weapon\Crossbow;
use Lemuria\Model\Lemuria\Commodity\Weapon\Spear;
use Lemuria\Model\Lemuria\Commodity\Weapon\Sword;
use Lemuria\Model\Lemuria\Commodity\Weapon\Warhammer;
use Lemuria\Model\Lemuria\Commodity\Wood;
use Lemuria\Model\Lemuria\Commodity\Woodshield;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Talent;
use Lemuria\Model\Lemuria\Talent\Archery;
use Lemuria\Model\Lemuria\Talent\Armory;
use Lemuria\Model\Lemuria\Talent\Bladefighting;
use Lemuria\Model\Lemuria\Talent\Bowmaking;
use Lemuria\Model\Lemuria\Talent\Camouflage;
use Lemuria\Model\Lemuria\Talent\Carriagemaking;
use Lemuria\Model\Lemuria\Talent\Catapulting;
use Lemuria\Model\Lemuria\Talent\Constructing;
use Lemuria\Model\Lemuria\Talent\Crossbowing;
use Lemuria\Model\Lemuria\Talent\Entertaining;
use Lemuria\Model\Lemuria\Talent\Espionage;
use Lemuria\Model\Lemuria\Talent\Fistfight;
use Lemuria\Model\Lemuria\Talent\Horsetaming;
use Lemuria\Model\Lemuria\Talent\Magic;
use Lemuria\Model\Lemuria\Talent\Mining;
use Lemuria\Model\Lemuria\Talent\Navigation;
use Lemuria\Model\Lemuria\Talent\Perception;
use Lemuria\Model\Lemuria\Talent\Quarrying;
use Lemuria\Model\Lemuria\Talent\Riding;
use Lemuria\Model\Lemuria\Talent\Roadmaking;
use Lemuria\Model\Lemuria\Talent\Shipbuilding;
use Lemuria\Model\Lemuria\Talent\Spearfighting;
use Lemuria\Model\Lemuria\Talent\Stamina;
use Lemuria\Model\Lemuria\Talent\Tactics;
use Lemuria\Model\Lemuria\Talent\Taxcollecting;
use Lemuria\Model\Lemuria\Talent\Trading;
use Lemuria\Model\Lemuria\Talent\Weaponry;
use Lemuria\Model\Lemuria\Talent\Woodchopping;

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
		'BENENNEN'     => 'NAME',
		'BESCHREIBEN'  => true,
		'BESTEIGEN'    => true,
		'BESTEUERN'    => 'TREIBEN',
		'BETRETEN'     => true,
		'BEWACHEN'     => true,
		'DEFAULT'      => 'VORLAGE',
		'EINHEIT'      => true,
		'EINTREIBEN'   => 'TREIBEN',
		'ENDE'         => true,
		'GIB'          => true,
		'GEBEN'        => 'GIB',
		'HELFEN'       => true,
		'ID'           => 'NUMMER',
		'KAEMPFEN'     => 'KÄMPFEN',
		'KÄMPFEN'      => true,
		'KOMMANDO'     => true,
		'KOMMENTAR'    => true,
		'KONTAKTIEREN' => true,
		'LEHREN'       => true,
		'LERNEN'       => true,
		'MACHEN'       => true,
		'NAME'         => true,
		'NÄCHSTER'     => true,
		'NAECHSTER'    => 'NÄCHSTER',
		'NUMMER'       => true,
		'PARTEI'       => true,
		'REKRUTIEREN'  => true,
		'RESERVIEREN'  => true,
		'SORTIEREN'    => true,
		'TARNEN'       => true,
		'TAUSCHEN'     => 'SORTIEREN',
		'TEXT'         => 'BESCHREIBEN',
		'TREIBEN'      => true,
		'UNTERHALTEN'  => true,
		'URSPRUNG'     => true,
		'ÜBERGEBEN'    => 'GIB',
		'VERLASSEN'    => true,
		'VORLAGE'      => true
	];

	/**
	 * @var array(string=>string)
	 */
	protected array $commodities = [
		'Armbrust'      => Crossbow::class,
		'Armbrueste'    => Crossbow::class,
		'Armbrüste'     => Crossbow::class,
		'Baeume'        => Tree::class,
		'Balsame'       => Balsam::class,
		'Baum'          => Tree::class,
		'Bäume'         => Tree::class,
		'Bogen'         => Bow::class,
		'Boegen'        => Bow::class,
		'Bögen'         => Bow::class,
		'Eisen'         => Iron::class,
		'Eisenschilde'  => Ironshield::class,
		'Elefanten'     => Elephant::class,
		'Erze'          => Ore::class,
		'Gewuerze'      => Spice::class,
		'Gewürze'       => Spice::class,
		'Granite'       => Granite::class,
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
		'Myrrhe'        => Myrrh::class,
		'Oele'          => Oil::class,
		'Öle'           => Oil::class,
		'Pelze'         => Fur::class,
		'Pferde'        => Horse::class,
		'Plattenpanzer' => Armor::class,
		'Schwerter'     => Sword::class,
		'Seide'         => Silk::class,
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

	public function __construct(protected Context $context) {
	}

	/**
	 * Create a Command.
	 *
	 * @throws UnknownCommandException
	 */
	public function create(Phrase $phrase): AbstractCommand {
		$verb = $this->identifyVerb(strtoupper($phrase->getVerb()));
		try {
			$command = match ($verb) {
				'BESCHREIBEN'  => Describe::class,
				'BESTEIGEN'    => Board::class,
				'BETRETEN'     => Enter::class,
				'BEWACHEN'     => Sentinel::class,
				'EINHEIT'      => Unit::class,
				'ENDE'         => End::class,
				'GIB'          => Handover::class,
				'HELFEN'       => Help::class,
				'KÄMPFEN'      => Fight::class,
				'KOMMANDO'     => Grant::class,
				'KOMMENTAR'    => Comment::class,
				'KONTAKTIEREN' => Contact::class,
				'LEHREN'       => Teach::class,
				'LERNEN'       => Learn::class,
				'MACHEN'       => Create::class,
				'NAME'         => Name::class,
				'NÄCHSTER'     => Next::class,
				'NUMMER'       => Number::class,
				'PARTEI'       => Party::class,
				'REKRUTIEREN'  => Recruit::class,
				'RESERVIEREN'  => Reserve::class,
				'SORTIEREN'    => Sort::class,
				'TARNEN'       => Disguise::class,
				'TREIBEN'      => Tax::class,
				'UNTERHALTEN'  => Entertain::class,
				'URSPRUNG'     => Origin::class,
				'VERLASSEN'    => Leave::class,
				'VORLAGE'      => DefaultCommand::class
			};
			return new $command($phrase, $this->context);
		} catch (\UnhandledMatchError) {
			throw new UnknownCommandException($phrase);
		}
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
			if (strpos($candidate, $verb) === 0) {
				if (is_string($isValid)) {
					$candidate = $isValid;
					$isValid   = $this->verbs[$isValid] ?? false;
				}
				if ($isValid === true) {
					$candidates[] = $candidate;
				}
			}
		}
		if (count($candidates) === 1) {
			return $candidates[0];
		}
		throw new UnknownCommandException($verb);
	}

	/**
	 * Match a Singleton.
	 */
	protected function identifySingleton(string $singleton, array $map): string {
		$singleton  = ucfirst(strtolower($singleton));
		$candidates = [];
		foreach ($map as $candidate => $singletonClass) {
			if (strpos($candidate, $singleton) === 0) {
				if ($candidate === $singleton) {
					return $singletonClass;
				}
				$candidates[] = $singletonClass;
			}
		}
		if (count($candidates) === 1) {
			return $candidates[0];
		}
		throw new UnknownCommandException();
	}
}
