<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\State;

/**
 * Base class for all complex commands that delegate to simpler commands.
 */
abstract class DelegatedEvent extends AbstractEvent
{
	protected array $delegates = [];

	public function __construct(State $state, int $priority) {
		parent::__construct($state, $priority);
		$this->createDelegates();
	}

	/**
	 * Get the delegates to execute.
	 */
	public function getDelegates(): array {
		return $this->delegates;
	}

	/**
	 * Create the delegates.
	 */
	abstract protected function createDelegates(): void;
}
