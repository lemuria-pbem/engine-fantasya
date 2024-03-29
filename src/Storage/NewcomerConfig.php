<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage;

use Lemuria\Model\Game;
use Lemuria\Statistics;

/**
 * A special game configuration that ignores all changes to game data but newcomer editing.
 */
class NewcomerConfig extends LemuriaConfig
{
	public function Game(): Game {
		return new NewcomerGame($this);
	}

	public function Statistics(): Statistics {
		return new NewcomerStatistics();
	}

}
