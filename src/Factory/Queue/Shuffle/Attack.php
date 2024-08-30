<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Queue\Shuffle;

use Lemuria\Engine\Fantasya\Command\Attack as AttackCommand;
use Lemuria\Engine\Fantasya\Factory\Queue\Shuffle;

class Attack implements Shuffle
{
	public function shuffle(array $queue): array {
		$shuffle = [];
		$units   = [];
		foreach ($queue as $action) {
			/** @var AttackCommand $action */
			$unit = $action->Unit();
			if ($unit->Construction() || $unit->Vessel()) {
				$shuffle[] = $action;
				continue;
			}

			$id = $action->Unit()->Id()->Id();
			if (!isset($units[$id])) {
				$units[$id] = [];
			}
			$units[$id][] = $action;
		}

		shuffle($units);
		foreach ($units as $actions) {
			array_push($shuffle, ...$actions);
		}
		return $shuffle;
	}
}
