<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Entity;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;

trait ActionTrait
{
	use BuilderTrait;

	private int $priority = Action::MIDDLE;

	private bool $isPrepared = false;

	#[Pure] public function Priority(): int {
		return $this->priority;
	}

	/**
	 * Check if the action has been prepared and is ready to execute.
	 */
	#[Pure] public function isPrepared(): bool {
		return $this->isPrepared;
	}

	protected function getPriority(): string {
		return match ($this->priority) {
			Action::BEFORE => 'B',
			Action::MIDDLE => 'M',
			Action::AFTER  => 'A',
			default        => throw new LemuriaException('Invalid action priority: ' . $this->priority)
		};
	}

	protected function setPriority(int $priority): void {
		$this->priority = $priority;
	}

	/**
	 * @throws CommandException
	 */
	protected function prepareAction(): void {
		try {
			$this->initialize();
			$this->isPrepared = true;
		} catch (CommandException $e) {
			throw $e;
		} catch (\Exception $e) {
			throw new CommandException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	  * @throws CommandException
	 */
	protected function executeAction(): void {
		try {
			$this->run();
		} catch (CommandException $e) {
			throw $e;
		} catch (\Exception $e) {
			throw new CommandException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * Make preparations before running the action.
	 */
	protected function initialize(): void {
	}

	/**
	 * The action implementation.
	 */
	protected function run(): void {
		throw new LemuriaException('This action cannot be executed directly.');
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
