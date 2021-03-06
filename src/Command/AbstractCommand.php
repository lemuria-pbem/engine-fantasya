<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\ActionTrait;
use Lemuria\Engine\Fantasya\Factory\ContextTrait;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait as ModelBuilderTrait;
use Lemuria\Model\Fantasya\Unit;

/**
 * Base class for all commands.
 */
abstract class AbstractCommand implements Command
{
	use ActionTrait;
	use ContextTrait;
	use ModelBuilderTrait;

	private static int $nextId = 0;

	private int $id;

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(protected Phrase $phrase, protected Context $context) {
		$this->id = self::$nextId++;
	}

	/**
	 * Get command as string.
	 */
	#[Pure] public function __toString(): string {
		return (string)$this->phrase;
	}

	/**
	 * Prepare the execution of the command.
	 *
	 * @throws CommandException
	 */
	public function prepare(): Action {
		Lemuria::Log()->debug('Preparing command ' . $this->phrase . '.', ['command' => $this]);
		$this->prepareAction();
		return $this;
	}

	/**
	 * Execute the command.
	 *
	 * @throws CommandException
	 */
	public function execute(): Action {
		Lemuria::Log()->debug('Executing command ' . $this->phrase . '.', ['command' => $this]);
		$this->executeAction();
		return $this;
	}

	/**
	 * Get the command ID.
	 */
	#[Pure] public function getId(): int {
		return $this->id;
	}

	/**
	 * Get the delegate to execute.
	 */
	#[Pure] public function getDelegate(): Command {
		return $this;
	}

	/**
	 * Get Unit from phrase parameter.
	 *
	 * @throws CommandException
	 */
	protected function nextId(int &$i): ?Unit {
		$id = $this->phrase->getParameter($i++);
		if (!$id) {
			return null;
		}

		if (strtoupper($id) === 'TEMP') {
			$id   = $this->phrase->getParameter($i++);
			$temp = $this->context->UnitMapper()->get($id);
			return $temp->getUnit();
		}

		try {
			return Unit::get(Id::fromId($id));
		} catch (NotRegisteredException $e) {
			throw new CommandException('Unit ' . $id . ' not found.', 0, $e);
		}
	}
}
