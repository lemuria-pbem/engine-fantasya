<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\Cartography;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\GazeOfTheGriffinAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\GazeOfTheGriffinMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\GazeOfTheGriffinNoneMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;

final class GazeOfTheGriffin extends AbstractCast
{
	use BuilderTrait;
	use MessageTrait;

	public function cast(): void {
		$unit   = $this->cast->Unit();
		$region = $this->findRegionFrom($unit->Region());
		if ($region) {
			$chronicle = $unit->Party()->Chronicle();
			if ($chronicle->has($region->Id())) {
				$this->message(GazeOfTheGriffinAlreadyMessage::class, $unit)->e($region);
			} else {
				$unit->Aura()->consume($this->cast->Aura());
				$chronicle->add($region);
				$this->addEffect($region, $unit->Party());
				$this->message(GazeOfTheGriffinMessage::class, $unit)->e($region);
			}
		} else {
			$this->message(GazeOfTheGriffinNoneMessage::class, $unit);
		}
	}

	private function findRegionFrom(Region $region): ?Region {
		$directions = $this->cast->Directions();
		if ($directions) {
			while ($directions->hasMore()) {
				$direction  = $directions->next();
				$neighbours = Lemuria::World()->getNeighbours($region);
				if (!isset($neighbours[$direction])) {
					return null;
				}
				$region = $neighbours[$direction];
			}
			return $region;
		}
		return null;
	}

	private function addEffect(Region $region, Party $party): void {
		$effect   = new Cartography(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setRegion($region));
		if (!$existing) {
			Lemuria::Score()->add($effect);
		} else {
			$effect = $existing;
		}
		$effect->Parties()->add($party);
	}
}
