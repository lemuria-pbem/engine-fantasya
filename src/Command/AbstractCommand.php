<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Factory\BuilderTrait;
use Lemuria\Engine\Lemuria\Factory\ContextTrait;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Engine\Lemuria\Exception\CommandException;
use Lemuria\Entity;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Lemuria\Factory\BuilderTrait as ModelBuilderTrait;
use Lemuria\Model\Lemuria\Unit;

/**
 * Base class for all commands.
 */
abstract class AbstractCommand implements Command
{
	use BuilderTrait;
	use ContextTrait;
	use ModelBuilderTrait;

	private static int $nextId = 0;

	private int $id;

	private bool $isPrepared = false;

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

	#[Pure] public function Priority(): int {
		return Action::MIDDLE;
	}

	/**
	 * Check if the action has been prepared and is ready to execute.
	 */
	#[Pure] public function isPrepared(): bool {
		return $this->isPrepared;
	}

	/**
	 * Prepare the execution of the command.
	 *
	 * @throws CommandException
	 */
	public function prepare(): Action {
		Lemuria::Log()->debug('Preparing command ' . $this->phrase . '.', ['command' => $this]);
		try {
			$this->initialize();
			$this->isPrepared = true;
		} catch (CommandException $e) {
			throw $e;
		} catch (\Exception $e) {
			throw new CommandException($e->getMessage(), $e->getCode(), $e);
		}
		return $this;
	}

	/**
	 * Execute the command.
	 *
	 * @throws CommandException
	 */
	public function execute(): Action {
		Lemuria::Log()->debug('Executing command ' . $this->phrase . '.', ['command' => $this]);
		try {
			$this->run();
		} catch (CommandException $e) {
			throw $e;
		} catch (\Exception $e) {
			throw new CommandException($e->getMessage(), $e->getCode(), $e);
		}
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
	 * Make preparations before running the command.
	 */
	protected function initialize(): void {
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		throw new LemuriaException('This command cannot be executed directly.');
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

	protected function message(string $messageType, ?Entity $target = null): LemuriaMessage {
		$id      = Lemuria::Report()->nextId();
		$message = new LemuriaMessage();
		return $this->initMessage($message, $target)->setType(self::createMessageType($messageType))->setId($id);
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $target ? $message->setAssignee($target->Id()) : $message;
	}
}
