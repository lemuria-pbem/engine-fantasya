<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Engine\Fantasya\Factory\Model\Season;
use Lemuria\Lemuria;

class Kraken extends AbstractBehaviour
{
	protected const CHANCE = 0.5;

	protected const REPRODUCTION = 1;

	/**
	 * Two or more krakens in the same region reproduce if there are no other krakens in the neighbour regions.
	 */
	public function Reproduction(): Reproduction {
		$reproduction = parent::Reproduction();
		if (Lemuria::Calendar()->Season() !== Season::WINTER) {
			$calculus = new Calculus($this->unit);
			$kinsmen  = $calculus->getKinsmen()->add($this->unit);
			if ($kinsmen->Size() >= 2) {
				if ($calculus->getRelatives()->count() <= 0) {
					$reproduction->setChance(self::CHANCE)->setSize(self::REPRODUCTION);
				}
			}
		}
		return $reproduction;
	}

	public function conduct(): Behaviour {
		return $this->reproduceAndLeaveOrRoam();
	}
}
