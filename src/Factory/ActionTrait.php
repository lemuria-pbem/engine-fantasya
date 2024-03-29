<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Exception\LemuriaException;

trait ActionTrait
{
	use MessageTrait;

	private Priority $priority = Priority::Middle;

	private int $preparation = 0;

	public function Priority(): Priority {
		return $this->priority;
	}

	/**
	 * Get command as string.
	 */
	public function __toString(): string {
		$phrase = (string)$this->phrase;
		if ($this->isAlternative()) {
			return CommandFactory::ALTERNATIVE_PREFIX . ' ' . $phrase;
		}
		return $phrase;
	}

	public function getInstruction(): string {
		if ($this->isAlternative()) {
			return CommandFactory::ALTERNATIVE_PREFIX . ' ' . $this->phrase;
		}
		return (string)$this->phrase;
	}

	/**
	 * Check if the action has been prepared and is ready to execute.
	 */
	public function isPrepared(): bool {
		return $this->preparation > 0;
	}

	/**
	 * Check if the action is an alternative action.
	 */
	public function isAlternative(): bool {
		return $this->preparation < 0 || $this->preparation > 1;
	}

	/**
	 * Mark the action as alternative.
	 */
	public function setAlternative(bool $isAlternative = true): void {
		if ($isAlternative) {
			if ($this->preparation === 0) {
				$this->preparation--;
			} elseif ($this->preparation === 1) {
				$this->preparation++;
			}
		} else {
			if ($this->preparation < 0) {
				$this->preparation = 0;
			} elseif ($this->preparation > 1) {
				$this->preparation = 1;
			}
		}
	}

	protected function getPriority(): string {
		return match ($this->priority) {
			Priority::Before => 'B',
			Priority::Middle => 'M',
			Priority::After  => 'A'
		};
	}

	protected function setPriority(Priority $priority): void {
		$this->priority = $priority;
	}

	/**
	 * @throws CommandException
	 */
	protected function prepareAction(): void {
		try {
			$this->initialize();
			if ($this->checkSize()) {
				$this->preparation = $this->preparation < 0 ? 2 : 1;
			}
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

	protected function checkSize(): bool {
		return true;
	}
}
