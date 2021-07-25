<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\ActivityProtocol;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\BrokenCarriageEffect;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Region\BrokenCarriageDiesMessage;
use Lemuria\Engine\Fantasya\Message\Region\BrokenCarriageMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Commodity\Carriage;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Orc;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

/**
 * A ragged orc strands with his carriage and dies.
 */
final class BrokenCarriage extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public const PARTY = 'party';

	public const REGION = 'region';

	public const CARGO = 'cargo';

	private const NPC = 1;

	private const NAME = 'Abgerissener Ork';

	private const DESCRIPTION = 'Ein ziemlich wild und heruntergekommen aussehender Ork, offensichtlich am Ende seiner Kräfte.';

	private const HEALTH = 0.5;

	private Party $party;

	private Region $region;

	public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
	}

	public function setOptions(array $options): BrokenCarriage {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->party  = Party::get(new Id($this->getOption(self::PARTY, 'int')));
		$this->region = Region::get(new Id($this->getOption(self::REGION, 'int')));
	}

	protected function run(): void {
		$effect = $this->getEffect();
		$unit   = $effect->Unit();
		if ($unit) {
			$unit->setSize(0);
			Lemuria::Score()->remove($effect);
			$this->message(BrokenCarriageDiesMessage::class, $unit->Region());
		} else {
			$unit      = $this->createOrc();
			$inventory = $unit->Inventory();
			$inventory->add(new Quantity(self::createCommodity(Horse::class), 2));
			$inventory->add(new Quantity(self::createCommodity(Carriage::class), 1));
			foreach ($this->getOption(self::CARGO, 'array') as $class => $count) {
				$inventory->add(new Quantity(self::createCommodity($class), $count));
			}
			Lemuria::Score()->add($effect->setUnit($unit));
			$this->message(BrokenCarriageMessage::class, $this->region);
		}
	}

	private function getEffect(): BrokenCarriageEffect {
		$effect = new BrokenCarriageEffect(State::getInstance());
		$effect->setParty($this->party);
		$existing = Lemuria::Score()->find($effect);
		if ($existing instanceof BrokenCarriageEffect) {
			return $existing;
		}
		return $effect;
	}

	private function createOrc(): Unit {
		$unit = new Unit();
		$race = self::createRace(Orc::class);
		$unit->setId(Lemuria::Catalog()->nextId(Catalog::UNITS));
		$unit->setSize(1)->setRace($race)->setHealth(self::HEALTH)->setDisguise();
		$unit->setName(self::NAME)->setDescription(self::DESCRIPTION);
		Party::get(new Id(self::NPC))->People()->add($unit);
		$this->region->Residents()->add($unit);
		$this->state->setProtocol(new ActivityProtocol($unit, new Context($this->state)));
		return $unit;
	}
}
