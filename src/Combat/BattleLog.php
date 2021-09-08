<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Engine\Fantasya\Combat\Log\AbstractMessage;
use Lemuria\Engine\Fantasya\Combat\Log\BattleBegins;

class BattleLog
{
	/**
	 * @var AbstractMessage[]
	 */
	protected array $log = [];

	public function __construct(protected Battle $battle) {
		$message = new BattleBegins();

		$this->log[] =
	}
}
