<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Lemuria;
use Lemuria\Model\Calendar\Season;

class Wolf extends AbstractBehaviour
{
	protected const GROW = 2;

	protected const SPLIT = 11;

	protected const GROW_MIN = 2;

	protected const GROW_MAX = 3;

	/**
	 * In Spring a group of wolves reproduces. If at any time there are more than ten wolves in the group, they split.
	 */
	public function Reproduction(): Reproduction {
		$reproduction = parent::Reproduction();
		$calendar     = Lemuria::Calendar();
		$size         = $this->unit->Size();

		if ($calendar->Season() === Season::Spring && $calendar->Week() === 1) {
			if ($size >= self::GROW) {
				$this->unit->setSize($size + rand(self::GROW_MIN, self::GROW_MAX));
				Lemuria::Log()->debug($this->unit . ' will reproduce this round.');
			}
		} else {
			if ($size >= self::SPLIT) {
				$split = (int)floor($size / 2);
				$this->unit->setSize($size - $split);
				$reproduction->setChance(1.0)->setSize($split);
				Lemuria::Log()->debug($this->unit . ' will split this round.');
			}

		}

		return $reproduction;
	}

	/**
	 * In Winter a group of wolves will attack smaller units.
	 */
	public function prepare(): Behaviour {
		if (!$this->hasRoamEffect() && Lemuria::Calendar()->Season() === Season::Winter) {
			return $this->prey()->attack();
		}
		return $this;
	}

	public function conduct(): Behaviour {
		return $this->reproduceAndLeaveOrRoam();
	}
}
