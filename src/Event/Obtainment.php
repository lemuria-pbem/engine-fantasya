<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Message\Party\ObtainmentMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Spell\AstralChaos;
use Lemuria\Model\Fantasya\Spell\AuraTransfer;
use Lemuria\Model\Fantasya\Spell\Daydream;
use Lemuria\Engine\Fantasya\Command\Cast\Earthquake;
use Lemuria\Model\Fantasya\Spell\Fireball;
use Lemuria\Model\Fantasya\Spell\Quacksalver;
use Lemuria\Model\Fantasya\Spell\Quickening;
use Lemuria\Model\Fantasya\Spell\ShockWave;
use Lemuria\Model\Fantasya\Spell\SongOfPeace;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

/**
 * Magicians gain new spells if their spell book is empty.
 */
final class Obtainment extends AbstractEvent
{
	use BuilderTrait;

	private const SPELL = [
		1 => Quacksalver::class,
		2 => SongOfPeace::class,
		3 => Fireball::class,
		4 => AuraTransfer::class,
		5 => ShockWave::class,
		6 => AstralChaos::class,
		7 => Daydream::class,
		8 => Quickening::class,
		9 => Earthquake::class
	];

	private Talent $magic;

	/**
	 * @var array(int=>Spell)
	 */
	private array $spell = [];

	private Unit $magician;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->magic = self::createTalent(Magic::class);
		foreach (self::SPELL as $difficulty => $class) {
			$spell = self::createSpell($class);
			if ($spell->Difficulty() !== $difficulty) {
				throw new LemuriaException('Wrong difficulty of spell ' . $spell . '.');
			}
			$this->spell[$difficulty] = $spell;
			Lemuria::Log()->debug('Obtainment spell for level ' . $difficulty . ' is ' . $spell . '.');
		}
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Party) as $party /* @var Party $party */) {
			if ($party->Type() !== Type::Player) {
				continue;
			}

			$magic = $this->getMagicLevel($party);
			if ($magic <= 0) {
				continue;
			}
			$spells    = array_fill(1, $magic, true);
			$spellBook = $party->SpellBook();
			foreach ($spellBook as $spell /* @var Spell $spell */) {
				$level = $spell->Difficulty();
				unset($spells[$level]);
			}
			foreach (array_keys($spells) as $level) {
				if (isset($this->spell[$level])) {
					$spell = $this->spell[$level];
					$spellBook->add($spell);
					$this->message(ObtainmentMessage::class, $party)->e($this->magician)->s($spell);
					Lemuria::Log()->debug('Party ' . $party->Id() . ' obtains level ' . $level . ' spell ' . $spell . '.');
				} else {
					Lemuria::Log()->debug('Party ' . $party->Id() . ' has no level ' . $level . ' spell.');
				}
			}
		}
	}

	private function getMagicLevel(Party $party): int {
		$level = 0;
		foreach ($party->People() as $unit /* @var Unit $unit */) {
			$ability = $this->context->getCalculus($unit)->knowledge($this->magic);
			$magic   = $ability->Level();
			if ($magic > $level) {
				$level = $magic;
				/* @var Unit $unit */
				$this->magician = $unit;
			}
		}
		return $level;
	}
}
