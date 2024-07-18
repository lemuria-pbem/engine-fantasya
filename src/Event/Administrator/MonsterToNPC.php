<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Effect\ControlEffect;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Unit;

/**
 * This event moves monster units to the NPC party.
 */
final class MonsterToNPC extends AbstractEvent
{
	use OptionsTrait;

	public final const string UNIT = 'unit';

	private Unit $unit;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function setOptions(array $options): MonsterToNPC {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->unit = Unit::get($this->getIdOption(self::UNIT));
	}

	protected function run(): void {
		$npc = $this->context->getTurnOptions()->Finder()->Party()->findByType(Type::NPC);
		$party = $this->unit->Party();
		switch ($party->Type()) {
			case Type::Player :
				Lemuria::Log()->critical('Player unit ' . $this->unit . ' cannot be owned by NPC party.');
				break;
			case Type::NPC :
				Lemuria::Log()->debug('NPC unit ' . $this->unit . ' is already in an NPC party.');
				break;
			default :
				$this->transferUnit($party, $npc);
				Lemuria::Log()->debug('Monster unit ' . $this->unit . ' is a member of ' . $npc . ' now.');
		}
	}

	private function transferUnit(Party $from, Party $to): void {
		$from->People()->remove($this->unit);
		$to->People()->add($this->unit);
		$effect   = new ControlEffect($this->state);
		$existing = Lemuria::Score()->find($effect->setUnit($this->unit));
		if ($existing instanceof ControlEffect) {
			Lemuria::Score()->remove($existing);
			Lemuria::Log()->debug('A ControlEffect has been removed from monster ' . $this->unit . '.');
		}
	}
}
