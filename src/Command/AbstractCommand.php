<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\Command\InvalidIdException;
use Lemuria\Engine\Fantasya\Exception\Command\UnitNotFoundException;
use Lemuria\Engine\Fantasya\Factory\ActionTrait;
use Lemuria\Engine\Fantasya\Factory\ContextTrait;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Exception\IdException;
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

	public static function id(): int {
		return self::$nextId++;
	}

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(protected Phrase $phrase, protected Context $context) {
		$this->id = self::id();
	}

	/**
	 * Get command as string.
	 */
	public function __toString(): string {
		return (string)$this->phrase;
	}

	/**
	 * Get the command ID.
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Get the delegate to execute.
	 */
	public function getDelegate(): Command {
		return $this;
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
	 * Get ID from phrase parameter.
	 *
	 * @throws InvalidIdException
	 */
	protected function parseId(int $i = 1): Id {
		$id = $this->phrase->getParameter($i);
		try {
			return Id::fromId($id);
		} catch (IdException $e) {
			throw new InvalidIdException($id, $e);
		}
	}

	/**
	 * Get Unit from phrase parameter.
	 *
	 * @throws CommandException
	 */
	protected function nextId(int &$i, ?string &$id = null): ?Unit {
		$id = $this->phrase->getParameter($i++);
		if (!$id) {
			return null;
		}

		if (strtoupper($id) === 'TEMP') {
			$id   = $this->phrase->getParameter($i++);
			$temp = $this->context->UnitMapper()->get($id);
			$id   = 'TEMP ' . $id;
			return $temp->getUnit();
		}

		try {
			$unit = Id::fromId($id);
			return Unit::get($unit);
		} catch (IdException $e) {
			throw new InvalidIdException($id, $e);
		} catch (NotRegisteredException $e) {
			/** @noinspection PhpUndefinedVariableInspection */
			throw new UnitNotFoundException($unit, $e);
		}
	}
}
