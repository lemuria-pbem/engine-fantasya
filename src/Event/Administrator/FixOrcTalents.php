<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Race;
use Lemuria\Model\Fantasya\Race\Orc;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Bladefighting;
use Lemuria\Model\Fantasya\Talent\Spearfighting;

/**
 * Make sure all orc units have the initial talents.
 */
final class FixOrcTalents extends AbstractEvent
{
	use BuilderTrait;

	private const int LEVEL = 1;

	private Race $orc;

	private Talent $bladeFighting;

	private Talent $spearFighting;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
		$this->orc           = self::createRace(Orc::class);
		$this->bladeFighting = self::createTalent(Bladefighting::class);
		$this->spearFighting = self::createTalent(Spearfighting::class);
	}

	protected function run(): void {
		foreach (Party::all() as $party) {
			if ($party->Race() === $this->orc) {
				foreach ($party->People() as $unit) {
					if ($unit->Race() === $this->orc) {
						$calculus = new Calculus($unit);
						$calculus->setAbility($this->bladeFighting, self::LEVEL);
						$calculus->setAbility($this->spearFighting, self::LEVEL);
					}
				}
			}
		}
	}
}
