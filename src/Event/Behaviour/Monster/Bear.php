<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Engine\Fantasya\Factory\Model\Season;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Gang;

class Bear extends AbstractBehaviour
{
	protected const CHANCE = 1.0;

	protected const REPRODUCTION = 1;

	/**
	 * In Summer a group of bears splits into individuals. If there are exactly two bears in a region, they reproduce.
	 */
	public function Reproduction(): Reproduction {
		$reproduction = parent::Reproduction();
		if (Lemuria::Calendar()->Season() === Season::SUMMER) {
			$size = $this->unit->Size();
			if ($size > 1) {
				$this->unit->setSize(1);
				$create = new Create($this->unit->Party(), $this->unit->Region());
				for ($i = 1; $i < $size; $i++) {
					$create->add(new Gang($this->race()));
				}
				$create->act();
			}
			$calculus = new Calculus($this->unit);
			$kinsmen  = $calculus->getKinsmen()->add($this->unit);
			if ($kinsmen->Size() === 2) {
				$reproduction->setChance(self::CHANCE)->setSize(self::REPRODUCTION);
			}
		}
		return $reproduction;
	}

	public function conduct(): Behaviour {
		return $this->reproduceAndLeaveOrRoam();
	}
}
