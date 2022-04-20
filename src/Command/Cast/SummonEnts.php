<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\VanishEffect;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Event\Behaviour\Monster\Ent as Behaviour;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\SummonEntsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\SummonEntsNoWoodMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Ent;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;

final class SummonEnts extends AbstractCast
{
	use BuilderTrait;

	private static function size(int $level): int {
		$size = 0;
		for ($i = 0; $i < $level; $i++) {
			$size += rand(4, 7);
		}
		return $size;
	}

	private static function weeks(): int {
		return rand(3, 5);
	}

	public function cast(): void {
		$unit   = $this->cast->Unit();
		$region = $unit->Region();
		if (!($region->Landscape() instanceof Wood)) {
			$this->message(SummonEntsNoWoodMessage::class, $unit);
			return;
		}

		$party = Party::get(Spawn::getPartyId(Type::MONSTER));
		$race  = self::createRace(Ent::class);
		$size  = self::size($this->cast->Level());
		$unit->Aura()->consume($this->cast->Aura());
		$create = new Create($party, $region);
		foreach ($create->add(new Gang($race, $size))->act()->getUnits() as $ents) {
			$effect = new VanishEffect(State::getInstance());
			Lemuria::Score()->add($effect->setUnit($ents)->setSummoner($unit)->setWeeks(self::weeks()));
			$this->message(SummonEntsMessage::class, $unit)->e($ents)->p($size);

			$behaviour = new Behaviour($ents);
			State::getInstance()->addMonster($behaviour->prepare());
			Lemuria::Log()->debug('Behaviour for summoned ents has been added.');
		}
	}
}
