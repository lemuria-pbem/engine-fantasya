<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity\Monster\Bear;
use Lemuria\Model\Fantasya\Commodity\Monster\Ghoul;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Wolf;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

final class MonsterParties extends AbstractEvent
{
	use BuilderTrait;

	private const IDS = ['m' => Goblin::class, 'z' => Zombie::class];

	private const IS_SENSING = [
		Bear::class   => 7,
		Ghoul::class  => 1,
		Wolf::class   => 4,
		Zombie::class => 1
	];

	private Talent $perception;

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
		$this->perception = self::createTalent(Perception::class);
	}

	protected function run(): void {
		foreach (self::IDS as $id => $race) {
			$id = Id::fromId($id);
			if (!Lemuria::Catalog()->has($id, Domain::PARTY)) {
				Lemuria::Log()->debug('Monster party ' . $id . ' does not exist.');
				continue;
			}
			$party = Party::get($id);
			$this->setToMonster($party, $race);
			foreach ($party->People() as $unit /* @var Unit $unit */) {
				$this->setPerception($unit);
			}
		}
	}

	private function setToMonster(Party $party, string $race): void {
		$race = self::createMonster($race);
		if ($party->Race() !== $race) {
			$party->setRace($race);
			Lemuria::Log()->debug('Setting race of monster party ' . $party . ' to ' . $race . '.');
		}
	}

	private function setPerception(Unit $unit): void {
		$sense = self::IS_SENSING[$unit->Race()::class] ?? 0;
		if ($sense > 0) {
			if (!$unit->Knowledge()->offsetExists($this->perception)) {
				$unit->Knowledge()->add(new Ability($this->perception, Ability::getExperience($sense)));
				Lemuria::Log()->debug('Monster ' . $unit . ' gains perception level ' . $sense . '.');
			}
		}
	}
}
