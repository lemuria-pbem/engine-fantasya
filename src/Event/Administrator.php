<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Administrator\DesertStones;
use Lemuria\Engine\Fantasya\Event\Administrator\HerbageFix;
use Lemuria\Engine\Fantasya\Event\Administrator\Overcrowded;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * The Administrator event adds some administrative tasks.
 */
final class Administrator extends DelegatedEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function createDelegates(): void {
		Lemuria::Log()->debug('Adding administrative events.');
		$this->delegates[] = new DesertStones($this->state);
		$this->delegates[] = new HerbageFix($this->state);
		$this->delegates[] = new Overcrowded($this->state);
	}
}
