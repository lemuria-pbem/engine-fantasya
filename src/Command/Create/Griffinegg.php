<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\AllocationCommand;
use Lemuria\Engine\Fantasya\Command\Attack as AttackCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\GriffinAttack;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Message\Unit\GriffineggAttackedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GriffineggAttackerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GriffineggChanceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GriffineggNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GriffineggOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GriffineggStealMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GriffineggStealOnlyMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Griffinegg as GriffineggModel;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Tactics;
use Lemuria\Model\Fantasya\Unit;

/**
 * Implementation of MACHEN Greifenei, which is essentially stealing griffin eggs from the region's resources, which can
 * result in an attack from breeding griffins.
 *
 * - MACHEN Greifenei|Greifeneier
 * - MACHEN <amount> Greifenei|Greifeneier
 */
final class Griffinegg extends AllocationCommand implements Activity
{
	use BuilderTrait;
	use DefaultActivityTrait;

	private const TACTICS = 4;

	private GriffineggModel $griffinegg;

	private int $demand;

	public function __construct(Phrase $phrase, Context $context, Job $job) {
		parent::__construct($phrase, $context);
		/** @var GriffineggModel $griffinegg */
		$griffinegg       = $job->getObject();
		$this->griffinegg = $griffinegg;
		$this->demand     = $job->Count();
	}

	protected function initialize(): void {
		if ($this->demand > 0) {
			$resources = $this->unit->Region()->Resources();
			$eggs      = $resources[$this->griffinegg];
			$eggCount  = $resources[$this->griffinegg]->Count();
			if ($eggCount > 0) {
				if ($this->demand > $eggCount) {
					$this->message(GriffineggOnlyMessage::class)->i($eggs);
				}
				$this->demand = min($this->demand, $eggCount);
				$chance       = 1.0;
				$personRate   = $resources[Griffin::class]->Count() / $this->unit->Size();
				if ($personRate > 0) {
					$camouflage = $this->calculus()->knowledge(Camouflage::class)->Level();
					$chance     = $camouflage > 0 ? $this->demand * $personRate / $camouflage : 0.0;
				}
				$this->message(GriffineggChanceMessage::class)->i($eggs)->p($chance, GriffineggChanceMessage::CHANCE);
				if (randChance($chance)) {
					$this->demand = 0;
					$this->attack($eggCount);
					$this->message(GriffineggAttackedMessage::class);
				}
			} else {
				$this->message(GriffineggNoneMessage::class);
			}
		}
		parent::initialize();
	}

	protected function run(): void {
		parent::run();
		if ($this->demand > 0) {
			$this->resources->rewind();
			$quantity = $this->resources->current();
			$eggs     = $quantity->Count();
			$this->unit->Inventory()->add($quantity);
			if ($eggs < $this->demand) {
				$this->message(GriffineggStealOnlyMessage::class)->i($quantity);
			} else {
				$this->message(GriffineggStealMessage::class)->i($quantity);
			}
		}
	}

	protected function createDemand(): void {
		if ($this->demand > 0) {
			$this->resources->add(new Quantity($this->griffinegg, $this->demand));
		}
	}

	private function attack(int $eggs): void {
		$region    = $this->unit->Region();
		$resources = $region->Resources();
		$griffins  = $resources[Griffin::class];

		$effect = $this->getEffect($region);
		if ($effect->Griffins()) {
			$unit = $effect->Griffins();
		} else {
			$unit = new Unit();
			$unit->setId(Lemuria::Catalog()->nextId(Domain::Unit));
			$unit->setName($griffins->Count() > 1 ? 'Greife' : 'Greif');
			/** @var Race $griffin */
			$griffin = $griffins->Commodity();
			$effect->setGriffins($unit->setRace($griffin)->setSize($griffins->Count())->setBattleRow(BattleRow::Aggressive));
			$party = Party::get(Spawn::getPartyId(Type::Monster));
			$party->People()->add($unit);
			$region->Residents()->add($unit);
			$unit->Knowledge()->add(new Ability(self::createTalent(Tactics::class), Ability::getExperience(self::TACTICS)));
			$unit->Inventory()->add(new Quantity($this->griffinegg, $eggs));
			$this->message(GriffineggAttackerMessage::class, $unit)->e($region)->i($griffins);
		}
		State::getInstance()->injectIntoTurn($effect);

		$id     = $this->unit->Id();
		$attack = new AttackCommand(new Phrase('ATTACKIEREN ' . $id), $this->context);
		State::getInstance()->injectIntoTurn($attack->from($unit));
	}

	private function getEffect(Region $region): GriffinAttack {
		$effect   = new GriffinAttack(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setRegion($region));
		if ($existing instanceof GriffinAttack) {
			return $existing;
		}
		Lemuria::Score()->add($effect);
		return $effect;
	}
}
