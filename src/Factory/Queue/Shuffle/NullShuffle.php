<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Queue\Shuffle;

use Lemuria\Engine\Fantasya\Factory\Queue\Shuffle;

class NullShuffle implements Shuffle
{
	public function shuffle(array $queue): array {
		return $queue;
	}
}
