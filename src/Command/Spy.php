<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\OneActivityTrait;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Espionage;
use Lemuria\Model\Fantasya\Talent\Perception;

final class Spy extends UnitCommand implements Activity
{
	private const SPY_BASE = 50;

	private const SPY_BONUS = 10;

	private const DISCOVER_BASE = 50;

	private const DISCOVER_BONUS = 5;

	use OneActivityTrait;

	protected function run(): void {
		if ($this->phrase->count() !== 1) {
			throw new UnknownCommandException($this);
		}
		$i    = 1;
		$unit = $this->nextId($i);

		if ($unit->Party() === $this->unit->Party()) {
			//TODO no need
			return;
		}
		$espionage = $this->calculus()->knowledge(Espionage::class)->Level();
		if ($espionage <= 0) {
			//TODO no chance
			return;
		}
		$outlook = new Outlook(new Census($unit->Party()));
		if ($outlook->Apparitions($this->unit->Region())->has($this->unit->Id())) {
			//TODO discovered
			//TODO spy is known
			return;
		}

		$calculus   = $this->context->getCalculus($unit);
		$camouflage = $calculus->knowledge(Camouflage::class)->Level();
		$spyLevel   = $espionage - $camouflage;
		$spySuccess = self::SPY_BASE + $spyLevel * self::SPY_BONUS;
		if (rand(1, 100) <= $spySuccess) {
			//TODO success
		} else {
			//TODO failed
		}

		$perception      = $calculus->knowledge(Perception::class)->Level();
		$discoverLevel   = $perception - $espionage;
		$discoverSuccess = self::DISCOVER_BASE + $discoverLevel * self::DISCOVER_BONUS;
		if (rand(1, 100) <= $discoverSuccess) {
			//TODO spy is unknown
		}
	}
}