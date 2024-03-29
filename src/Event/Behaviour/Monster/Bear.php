<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Lemuria;
use Lemuria\Model\Calendar\Season;
use Lemuria\Model\Fantasya\Gang;

class Bear extends AbstractBehaviour
{
	protected const float CHANCE = 1.0;

	protected const int REPRODUCTION = 1;

	protected const float PERISH = 0.01;

	/**
	 * In Summer a group of bears splits into individuals. If there are exactly two bears in a region, they reproduce.
	 */
	public function Reproduction(): Reproduction {
		$reproduction = parent::Reproduction();
		if (Lemuria::Calendar()->Season() === Season::Summer) {
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
				if ($reproduction->Chance() > 0.0) {
					Lemuria::Log()->debug($this->unit . ' will reproduce this round.');
				}
			}
		}
		return $reproduction;
	}

	public function prepare(): static {
		if (Lemuria::Calendar()->Season() === Season::Winter) {
			$this->hibernate();
		}
		return $this;
	}

	public function conduct(): static {
		$this->perishByChance(self::PERISH);
		if ($this->hibernate) {
			return $this;
		}
		return $this->reproduceAndLeaveOrRoam();
	}
}
