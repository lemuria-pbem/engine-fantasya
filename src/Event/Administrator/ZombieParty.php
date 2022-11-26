<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Event\Game\Spawn;
use Lemuria\Engine\Fantasya\Factory\Model\SimpleNewcomer;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;

/**
 * This event searches for overcrowded constructions.
 */
final class ZombieParty extends AbstractEvent
{
	private const ID = 'z';

	private const UUID = '052f2829-a785-45f0-96a5-39e3394b9bf1';

	private const NAME = 'Zombies';

	use BuilderTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
	}

	protected function run(): void {
		$id = Id::fromId(self::ID);
		if (Lemuria::Catalog()->has($id, Domain::PARTY)) {
			Lemuria::Log()->debug('Zombie party ' . $id . ' already exists.');
			return;
		}

		$from   = Party::get(Spawn::getPartyId(Type::MONSTER));
		$origin = $from->Origin();
		$zombie = self::createMonster(Zombie::class);
		$to     = new Party(new SimpleNewcomer(self::UUID, time()), Type::MONSTER);
		$to->setRace($zombie)->setOrigin($origin)->setName(self::NAME)->setId($id);
		$to->Chronicle()->add($origin);

		$migrants = new People();
		foreach ($from->People() as $unit /* @var Unit $unit */) {
			if ($unit->Race() === $zombie) {
				$migrants->add($unit);
			}
		}
		foreach ($migrants as $unit /* @var Unit $unit */) {
			$from->People()->remove($unit);
			$to->People()->add($unit);
			Lemuria::Log()->debug('Zombie unit ' . $unit->Id() . ' moved to new Zombie party ' . $id . '.');
		}
	}
}
