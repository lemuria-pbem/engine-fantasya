<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Event;
use Lemuria\Engine\Lemuria\Exception\CommandException;
use Lemuria\Engine\Lemuria\Factory\ActionTrait;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;

abstract class AbstractEvent implements Event
{
	use ActionTrait;

	#[Pure] public function __construct(protected State $state, int $priority = Action::MIDDLE) {
		$this->setPriority($priority);
	}

	/**
	 * Get action as string.
	 */
	#[Pure] public function __toString(): string {
		return 'Event ' . $this->getPriority() . ': ' . getClass($this);
	}

	/**
	 * Prepare the execution of the event.
	 *
	 * @throws CommandException
	 */
	public function prepare(): Action {
		Lemuria::Log()->debug('Preparing event ' . $this . '.');
		$this->prepareAction();
		return $this;
	}

	/**
	 * Execute the event.
	 *
	 * @throws CommandException
	 */
	public function execute(): Action {
		Lemuria::Log()->debug('Executing command ' . $this . '.');
		$this->executeAction();
		return $this;
	}
}
