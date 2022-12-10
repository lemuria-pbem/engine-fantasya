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

	private bool $isPrepared = false;

	public function Priority(): Priority {
		return $this->priority;
	}

	/**
	 * Check if the action has been prepared and is ready to execute.
	 */
	public function isPrepared(): bool {
		return $this->isPrepared;
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
				$this->isPrepared = true;
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
