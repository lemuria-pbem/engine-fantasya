<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Aura;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

/**
 * Fix maximum aura of all magicians.
 */
final class FixAuraMaximum extends AbstractEvent
{
	use BuilderTrait;

	private Talent $magic;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->magic = self::createTalent(Magic::class);
	}

	protected function run(): void {
		foreach (Unit::all() as $unit) {
			if ($unit->Knowledge()->offsetExists($this->magic)) {
				$aura = $unit->Aura();
				if (!$aura) {
					$aura = new Aura();
					$unit->setAura($aura);
					Lemuria::Log()->critical('Magician ' . $unit . ' had no Aura, created one.');
				}

				$calculus = new Calculus($unit);
				$level    = $calculus->ability($this->magic)->Level();
				$maximum  = $level > 1 ? $level ** 2 : 1;
				$old      = $aura->Maximum();
				if ($maximum !== $old) {
					$aura->setMaximum($maximum);
					Lemuria::Log()->critical('Magician ' . $unit . ' had maximum aura of ' . $old . ', set to ' . $maximum . '.');
				}
			}
		}
	}
}
