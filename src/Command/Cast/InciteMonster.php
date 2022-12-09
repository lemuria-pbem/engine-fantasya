<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Command\Attack as AttackCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\InciteMonsterNoEnemiesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\InciteMonsterNoMessage;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

final class InciteMonster extends AbstractCast
{
	use MessageTrait;

	public function cast(): void {
		$unit    = $this->cast->Unit();
		$aura    = $this->cast->Aura();
		$monster = $this->cast->Target();
		if ($aura > 0 && $monster) {
			if ($monster->Party()->Type() === Type::Monster) {
				$enemies = $this->getEnemies($unit, $monster);
				if (empty($enemies)) {
					$this->message(InciteMonsterNoEnemiesMessage::class, $unit);
				} else {
					$unit->Aura()->consume($aura);
					$state   = State::getInstance();
					$context = new Context($state);
					$context->setUnit($monster);
					$phrase = new Phrase('ATTACKIEREN ' . implode(' ', $enemies));
					$attack = new AttackCommand($phrase, $context);
					$state->injectIntoTurn($attack);
				}
			} else {
				$this->message(InciteMonsterNoMessage::class, $unit)->e($monster);
			}
		}
	}

	private function getEnemies(Unit $unit, Unit $monster): array {
		$ids = [];
		$ourParty  = $unit->Party();
		$diplomacy = $ourParty->Diplomacy();
		$region    = $unit->Region();
		$outlook   = new Outlook(new Census($ourParty));
		$targets   = new Outlook(new Census($monster->Party()));
		$enemies   = $targets->getApparitions($region);
		foreach ($outlook->getApparitions($region) as $enemy /* @var Unit $enemy */) {
			if ($enemy->Party() === $ourParty) {
				continue;
			}
			if ($diplomacy->has(Relation::COMBAT, $enemy, $region)) {
				continue;
			}
			$id = $enemy->Id();
			if ($enemies->has($id)) {
				$ids[] = (string)$id;
			}
		}
		return $ids;
	}
}
