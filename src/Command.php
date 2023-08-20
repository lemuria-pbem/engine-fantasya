<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

/**
 * Commands are executed by a Unit.
 */
interface Command extends Action
{
	/**
	 * Get the command phrase.
	 */
	public function Phrase(): Phrase;

	/**
	 * Get the command ID.
	 */
	public function getId(): int;

	/**
	 * Get the delegate to execute.
	 */
	public function getDelegate(): Command;

	/**
	 * Get the instruction string.
	 */
	public function getInstruction(): string;
}
