<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Effect\SpyEffect;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\OneActivityTrait;
use Lemuria\Engine\Fantasya\Message\Party\SpyRevealedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SpyDiscoveredMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SpyFailedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SpyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SpyNoChanceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SpyNotHereMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SpyNotRevealedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SpyOwnUnitMessage;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Espionage;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

/**
 * Implementation of SPIONIEREN (spy).
 *
 * - SPIONIEREN <unit>
 */
final class Spy extends UnitCommand implements Activity
{
	use OneActivityTrait;

	public const LEVEL_REVEAL_DISGUISE = 5;

	private const SPY_BASE = 50;

	private const SPY_BONUS = 10;

	private const DISCOVER_BASE = 50;

	private const DISCOVER_BONUS = 5;

	protected function run(): void {
		if ($this->phrase->count() !== 1) {
			throw new UnknownCommandException($this);
		}
		$i    = 1;
		$unit = $this->nextId($i);
		$region = $this->unit->Region();
		if ($unit->Region() !== $region || !$this->calculus()->canDiscover($unit)) {
			$this->message(SpyNotHereMessage::class)->e($unit);
			return;
		}

		$party = $unit->Party();
		if ($party === $this->unit->Party()) {
			$this->message(SpyOwnUnitMessage::class)->e($unit);
			return;
		}
		$espionage = $this->calculus()->knowledge(Espionage::class)->Level();
		if ($espionage <= 0) {
			$this->message(SpyNoChanceMessage::class);
			return;
		}

		if ($this->context->getTurnOptions()->IsSimulation()) {
			$this->message(SpyFailedMessage::class)->e($unit);
			return;
		}

		$outlook = new Outlook(new Census($party));
		if ($outlook->getApparitions($region)->has($this->unit->Id())) {
			$this->message(SpyDiscoveredMessage::class)->e($unit);
			$this->message(SpyRevealedMessage::class, $party)->e($region)->e($this->unit, SpyRevealedMessage::UNIT);
			return;
		}

		$calculus   = $this->context->getCalculus($unit);
		$camouflage = $calculus->knowledge(Camouflage::class)->Level();
		$spyLevel   = $espionage - $camouflage;
		$spySuccess = self::SPY_BASE + $spyLevel * self::SPY_BONUS;
		if (randInt(1, 100) <= $spySuccess) {
			$this->addSpyEffect($unit, $spyLevel);
			$this->message(SpyMessage::class)->e($unit);
		} else {
			$this->message(SpyFailedMessage::class)->e($unit);
		}

		$perception      = $calculus->knowledge(Perception::class)->Level();
		$discoverLevel   = $perception - $espionage;
		$discoverSuccess = self::DISCOVER_BASE + $discoverLevel * self::DISCOVER_BONUS;
		if (randInt(1, 100) <= $discoverSuccess) {
			$this->message(SpyNotRevealedMessage::class, $unit);
		}
	}

	private function addSpyEffect(Unit $unit, int $spyLevel): void {
		$effect = new SpyEffect(State::getInstance());
		$effect->setParty($this->unit->Party());
		$existing = Lemuria::Score()->find($effect);
		if ($existing) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
		}
		$effect->addTarget($unit, $spyLevel);
	}
}
