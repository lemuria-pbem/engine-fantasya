<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Event\Administrator\Overcrowded;
use Lemuria\Engine\Fantasya\Factory\ReflectionTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * The Administrator event adds some administrative tasks.
 */
final class Administrator extends DelegatedEvent
{
	use ReflectionTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function add(string $class): static {
		$this->validateEventClass($class);
		$this->delegates[] = $class;
		return $this;
	}

	protected function createDelegates(): void {
		Lemuria::Log()->debug('Adding administrative events.');
		$this->delegates[] = new Overcrowded($this->state);
	}
}
