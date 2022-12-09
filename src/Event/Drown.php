<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Message\Unit\DrownMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

/**
 * Units on open sea will drown.
 */
final class Drown extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Location) as $region /* @var Region $region */) {
			if ($region->Landscape() instanceof Ocean) {
				foreach ($region->Residents() as $unit /* @var Unit $unit */) {
					if ($unit->Size() > 0 && $unit->Party()->Type() === Type::Player && !$unit->Vessel()) {
						$unit->setHealth(0.0);
						$unit->setSize(0);
						$this->message(DrownMessage::class, $unit)->e($region);
					}
				}
			}
		}
	}
}
