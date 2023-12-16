<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Effect\Contagion;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\Model\Disease;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Region;

/**
 * The Goblin Plague hits a region for a bunch of rounds, causing all infected Goblin units to lose their camouflage
 * talent and stay in the region until the plague vanishes.
 */
final class GoblinPlague extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const REGION = 'region';

	public final const DURATION = 'duration';

	private Region $region;

	private int $duration;

	private Monster $goblin;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->goblin = self::createMonster(Goblin::class);
	}

	public function setOptions(array $options): GoblinPlague {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->region   = Region::get($this->getIdOption(self::REGION));
		$this->duration = $this->getOption(self::DURATION, 'int');
	}

	protected function run(): void {
		$effect = $this->getEffect();
		foreach ($this->region->Residents() as $unit) {
			if ($unit->Race() === $this->goblin) {
				$effect->Units()->add($unit);
			}
		}
	}

	private function getEffect(): Contagion {
		$effect   = new Contagion($this->state);
		$existing = Lemuria::Score()->find($effect->setRegion($this->region));
		if ($existing instanceof Contagion) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
			$this->state->injectIntoTurn($effect);
		}
		$effect->Units()->clear();
		return $effect->setDisease(Disease::GoblinPlague)->setDuration($this->duration);
	}
}
