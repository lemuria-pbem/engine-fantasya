<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Event;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\ActionTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

abstract class AbstractEvent implements Event
{
	use ActionTrait;

	protected Context $context;

	public function __construct(protected State $state, Priority $priority) {
		$this->setPriority($priority);
		$this->context = new Context($state);
	}

	/**
	 * Get action as string.
	 */
	public function __toString(): string {
		return 'Event[' . $this->getPriority() . '] ' . getClass($this);
	}

	/**
	 * Prepare the execution of the Event.
	 *
	 * @throws CommandException
	 */
	public function prepare(): Action {
		Lemuria::Log()->debug('Preparing ' . $this . '.');
		$this->prepareAction();
		return $this;
	}

	/**
	 * Execute the Event.
	 *
	 * @throws CommandException
	 */
	public function execute(): Action {
		Lemuria::Log()->debug('Executing ' . $this . '.');
		$this->executeAction();
		return $this;
	}
}
