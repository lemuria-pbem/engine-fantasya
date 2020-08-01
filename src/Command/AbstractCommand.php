<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Factory\BuilderTrait;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Message\MessageType;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Engine\Lemuria\Exception\CommandException;
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
	use ModelBuilderTrait;

	protected Phrase $phrase;

	protected Context $context;

	private static int $nextId = 0;

	private int $id;

	/**
	 * Create a new command for given Phrase.
	 *
	 * @param Phrase $phrase
	 * @param Context $context
	 */
	public function __construct(Phrase $phrase, Context $context) {
		$this->phrase  = $phrase;
		$this->context = $context;
		$this->id      = self::$nextId++;
	}

	/**
	 * Get command as string.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return (string)$this->phrase;
	}

	/**
	 * Get the priority.
	 *
	 * @return int
	 */
	public function Priority(): int {
		return Action::MIDDLE;
	}

	/**
	 * Execute the command.
	 *
	 * @return Action
	 * @throws CommandException
	 */
	public function execute(): Action {
		Lemuria::Log()->debug('Executing command ' . $this->phrase . '.', ['command' => $this]);
		try {
			$this->prepare();
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
	 *
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Get the delegate to execute.
	 *
	 * @return Command
	 */
	public function getDelegate(): Command {
		return $this;
	}

	/**
	 * Make preparations before running the command.
	 */
	protected function prepare(): void {
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
	 * @param int $i
	 * @return Unit|null
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

	/**
	 * @param string $messageType
	 * @return LemuriaMessage
	 */
	protected function message(string $messageType): LemuriaMessage {
		$message = new LemuriaMessage();
		$message->setId(Lemuria::Report()->nextId())->setType(self::createMessageType($messageType));
		Lemuria::Report()->register($message);
		return $this->initMessage($message);
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function initMessage(LemuriaMessage $message): LemuriaMessage {
		return $message;
	}
}
