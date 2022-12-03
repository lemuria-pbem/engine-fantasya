<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;

final class MonsterParties extends AbstractEvent
{
	use BuilderTrait;

	private const IDS = ['m' => Goblin::class, 'z' => Zombie::class];

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
	}

	protected function run(): void {
		foreach (self::IDS as $id => $race) {
			$id = Id::fromId($id);
			if (!Lemuria::Catalog()->has($id, Domain::PARTY)) {
				Lemuria::Log()->debug('Monster party ' . $id . ' does not exist.');
				continue;
			}
			$this->setToMonster(Party::get($id), $race);
		}
	}

	private function setToMonster(Party $party, string $race): void {
		$race = self::createMonster($race);
		if ($party->Race() !== $race) {
			$party->setRace($race);
			Lemuria::Log()->debug('Setting race of monster party ' . $party . ' to ' . $race . '.');
		}
	}
}
