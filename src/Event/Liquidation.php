<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use Lemuria\Engine\Lemuria\Action;

/**
 * Remove empty units at the end of a turn.
 */
final class Liquidation extends AbstractEvent
{
	public function __construct() {
		$this->setPriority(Action::AFTER);
	}

	protected function initialize(): void {
		//TODO
	}

	protected function run(): void {
		//TODO
	}
}
