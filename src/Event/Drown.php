<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Message\Unit\DrownMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Region;

/**
 * Units on open sea will drown.
 */
final class Drown extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		foreach (Region::all() as $region) {
			if ($region->Landscape() instanceof Navigable) {
				foreach ($region->Residents() as $unit) {
					if ($unit->Size() > 0 && $unit->Party()->Type() !== Type::Monster && !$unit->Vessel()) {
						$unit->setHealth(0.0);
						$unit->setSize(0);
						$this->message(DrownMessage::class, $unit)->e($region);
					}
				}
			}
		}
	}
}
