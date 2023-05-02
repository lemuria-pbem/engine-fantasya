<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Race;
use Lemuria\Model\Fantasya\Race\Orc;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Bladefighting;
use Lemuria\Model\Fantasya\Talent\Spearfighting;
use Lemuria\Model\Fantasya\Unit;

/**
 * Make sure all orc units have the initial talents.
 */
final class FixOrcTalents extends AbstractEvent
{
	use BuilderTrait;

	private const LEVEL = 1;

	private Race $orc;

	private Talent $bladeFighting;

	private Talent $spearFighting;

	private int $experience;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->orc           = self::createRace(Orc::class);
		$this->bladeFighting = self::createTalent(Bladefighting::class);
		$this->spearFighting = self::createTalent(Spearfighting::class);
		$this->experience    = Ability::getExperience(self::LEVEL);
	}

	protected function run(): void {
		foreach (Party::all() as $party) {
			if ($party->Race() === $this->orc) {
				foreach ($party->People() as $unit) {
					if ($unit->Race() === $this->orc) {
						$this->fixTalent($unit, $this->bladeFighting);
						$this->fixTalent($unit, $this->spearFighting);
					}
				}
			}
		}
	}

	private function fixTalent(Unit $unit, Talent $talent): void {
		$knowledge = $unit->Knowledge();
		if (isset($knowledge[$talent])) {
			$ability    = $knowledge[$talent];
			$experience = $this->experience - $ability->Experience();
			if ($experience > 0) {
				$knowledge->add(new Ability($talent, $experience));
				Lemuria::Log()->debug('Fixing unit ' . $unit . ': Add ' . $experience . ' experience in ' . $talent . '.');
			}
		} else {
			$knowledge->add(new Ability($talent, $this->experience));
			Lemuria::Log()->debug('Adding ' . $talent . ' level ' . self::LEVEL . ' to unit ' . $unit . '.');
		}
	}
}
