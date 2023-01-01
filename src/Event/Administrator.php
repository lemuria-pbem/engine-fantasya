<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Administrator\ConvertLakes;
use Lemuria\Engine\Fantasya\Event\Administrator\LocationNames;
use Lemuria\Engine\Fantasya\Event\Administrator\Overcrowded;
use Lemuria\Engine\Fantasya\Event\Administrator\MonsterParties;
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
		$this->delegates[] = new Overcrowded($this->state);
		$this->delegates[] = new MonsterParties($this->state);
		$this->delegates[] = new LocationNames($this->state);
		$this->delegates[] = new ConvertLakes($this->state);
	}
}
