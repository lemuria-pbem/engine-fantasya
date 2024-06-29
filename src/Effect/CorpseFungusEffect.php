<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\Unit\CorpseFungusMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Skeleton;
use Lemuria\Model\Fantasya\Commodity\Monster\Zombie;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Talent\Bladefighting;

final class CorpseFungusEffect extends AbstractUnitEffect
{
	use BuilderTrait;
	use GrammarTrait;

	private const array TALENTS = [Bladefighting::class => 5];

	private Monster $skeleton;

	private Monster $zombie;

	private Party $monsters;

	private Party $zombies;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->skeleton = self::createMonster(Skeleton::class);
		$this->zombie   = self::createMonster(Zombie::class);
		$this->monsters = $state->getTurnOptions()->Finder()->Party()->findByType(Type::Monster);
		$this->zombies  = $state->getTurnOptions()->Finder()->Party()->findByRace($this->zombie);
	}

	protected function run(): void {
		$unit = $this->Unit();
		if ($unit->Race() === $this->zombie) {
			if ($unit->Size() > 0) {
				$name = $this->translateSingleton($this->skeleton, $unit->Size() === 1 ? 0 : 1, Casus::Nominative);
				$unit->setRace($this->skeleton)->setName($name);
				$calculus = new Calculus($unit);
				foreach (self::TALENTS as $talent => $level) {
					$calculus->setAbility($talent, $level);
				}
				if ($unit->Party() === $this->zombies) {
					$this->zombies->People()->remove($unit);
					$this->monsters->People()->add($unit);
				}
				$this->message(CorpseFungusMessage::class, $unit)->s($this->zombie)->s($this->skeleton, CorpseFungusMessage::TURNED);
			}
		} else {
			throw new LemuriaException('How did ' . $unit . ' got infected with the Corpse Fungus?');
		}
		Lemuria::Score()->remove($this);
	}
}
