<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Lemuria;
use Lemuria\Model\Calendar\Season;

class Wolf extends AbstractBehaviour
{
	protected const int GROW = 2;

	protected const int SPLIT = 11;

	protected const int GROW_MIN = 2;

	protected const int GROW_MAX = 3;

	protected const float PERISH = 0.01;

	/**
	 * In Spring a group of wolves reproduces. If at any time there are more than ten wolves in the group, they split.
	 */
	public function Reproduction(): Reproduction {
		$reproduction = parent::Reproduction();
		$calendar     = Lemuria::Calendar();
		$size         = $this->unit->Size();

		if ($calendar->Season() === Season::Spring && $calendar->Week() === 1) {
			if ($size >= self::GROW) {
				$this->unit->setSize($size + randInt(self::GROW_MIN, self::GROW_MAX));
				if ($reproduction->Chance() > 0.0) {
					Lemuria::Log()->debug($this->unit . ' will reproduce this round.');
				}
			}
		} else {
			if ($size >= self::SPLIT) {
				$split = (int)floor($size / 2);
				$this->unit->setSize($size - $split);
				$reproduction->setChance(1.0)->setSize($split);
				if ($reproduction->Chance() > 0.0) {
					Lemuria::Log()->debug($this->unit . ' will split this round.');
				}
			}

		}

		return $reproduction;
	}

	/**
	 * In Winter a group of wolves will attack smaller units.
	 */
	public function prepare(): static {
		if (!$this->hasRoamEffect() && Lemuria::Calendar()->Season() === Season::Winter) {
			return $this->prey()->attack();
		}
		return $this;
	}

	public function conduct(): static {
		return $this->perishByChance(self::PERISH)->reproduceAndLeaveOrRoam();
	}
}
