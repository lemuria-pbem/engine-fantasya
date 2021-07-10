<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Command\Teach;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\StringList;

class LemuriaInstructions extends StringList
{
	/**
	 * @var array(int=>UnitCommand)
	 */
	private array $commands = [];

	/**
	 * @param int $offset
	 * @param UnitCommand|string $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void {
		if ($value instanceof UnitCommand) {
			if ($this->offsetExists($offset)) {
				$this->commands[$offset] = $value;
			} else {
				$this->commands[] = $value;
			}
			$value = (string)$value->Phrase();
		}
		parent::offsetSet($offset, $value);
	}

	/**
	 * @param int $offset
	 */
	public function offsetUnset(mixed $offset): void {
		if ($this->offsetExists($offset)) {
			parent::offsetUnset($offset);
			unset($this->commands[$offset]);
			$this->commands = array_values($this->commands);
		}
	}

	public function replace(string $oldId, ?string $newId): void {
		$newId = $newId ? ' ' . $newId : '';
		$n     = count($this->commands);
		for ($i = $n - 1; $i >= 0; $i--) {
			/** @var UnitCommand $command */
			$command = $this->commands[$i];
			if ($command) {
				switch ($command::class) {
					case Teach::class :
						parent::offsetSet($i, str_replace(' ' . $oldId, $newId, parent::offsetGet($i)));
				}
			}
		}
	}
}
