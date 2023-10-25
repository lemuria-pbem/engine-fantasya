<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Statistics\CensusWorkers;
use Lemuria\Engine\Fantasya\Event\Statistics\Colonies;
use Lemuria\Engine\Fantasya\Event\Statistics\Economy;
use Lemuria\Engine\Fantasya\Event\Statistics\Education;
use Lemuria\Engine\Fantasya\Event\Statistics\Ethnology;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * The Statistics master event defines subtasks that together create the game statistics for reporting.
 */
final class Statistics extends DelegatedEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function createDelegates(): void {
		Lemuria::Log()->debug('Adding statistics tasks.');
		$this->delegates[] = new CensusWorkers($this->state);
		$this->delegates[] = new Colonies($this->state);
		$this->delegates[] = new Economy($this->state);
		$this->delegates[] = new Education($this->state);
		$this->delegates[] = new Ethnology($this->state);
	}
}
