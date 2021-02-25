<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Effect;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Effect;
use Lemuria\Engine\Lemuria\Exception\CommandException;
use Lemuria\Engine\Lemuria\Factory\ActionTrait;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;

abstract class AbstractEffect implements Effect
{
	use ActionTrait;

	protected Context $context;

	#[Pure] public function __construct(protected State $state, int $priority = Action::MIDDLE) {
		$this->setPriority($priority);
		$this->context = new Context($state);
	}

	/**
	 * Get action as string.
	 */
	#[Pure] public function __toString(): string {
		return 'Effect[' . $this->getPriority() . '] ' . getClass($this);
	}

	/**
	 * Prepare the execution of the Effect.
	 *
	 * @throws CommandException
	 */
	public function prepare(): Action {
		Lemuria::Log()->debug('Preparing ' . $this . '.');
		$this->prepareAction();
		return $this;
	}

	/**
	 * Execute the Effect.
	 *
	 * @throws CommandException
	 */
	public function execute(): Action {
		Lemuria::Log()->debug('Executing ' . $this . '.');
		$this->executeAction();
		return $this;
	}
}
