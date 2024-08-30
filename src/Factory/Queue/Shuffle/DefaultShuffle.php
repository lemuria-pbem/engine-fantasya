<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Queue\Shuffle;

use Lemuria\Engine\Fantasya\Factory\Queue\Shuffle;
use Lemuria\Engine\Fantasya\Factory\RealmTrait;

class DefaultShuffle implements Shuffle
{
	use RealmTrait;

	public function shuffle(array $queue): array {
		$shuffle = [];
		$units   = [];
		foreach ($queue as $action) {
			if ($this->isRealmCommand($action)) {
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
