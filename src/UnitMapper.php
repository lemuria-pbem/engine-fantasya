<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Command\Create\Temp;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Command\Exception\TempUnitException;
use Lemuria\Model\Fantasya\Unit;

/**
 * Helper class for mapping newly created units with TEMP numbers.
 */
class UnitMapper
{
	/**
	 * @var array(string=>Temp)
	 */
	private array $map = [];

	/**
	 * @var array(int=>string)
	 */
	private array $temp = [];

	/**
	 * Check if temp unit is already mapped.
	 */
	#[Pure] public function has(string $tempNumber): bool {
		return isset($this->map[$tempNumber]);
	}

	/**
	 * Add newly created Unit to mapper.
	 *
	 * @throws TempUnitException
	 */
	public function map(Temp $command): UnitMapper {
		$temp = $command->getTempNumber();
		if ($temp) {
			if ($this->has($temp)) {
				throw new TempUnitException('TEMP unit ' . $temp . ' is mapped already.');
			}
			try {
				$id               = $command->getUnit()->Id()->Id();
				$this->map[$temp] = $command;
				$this->temp[$id]  = $temp;
			} catch (CommandException $e) {
				throw new TempUnitException($e->getMessage(), $e);
			}
		}
		return $this;
	}

	/**
	 * Get newly created Unit by TEMP number.
	 *
	 * @throws TempUnitException
	 */
	public function get(string $temp): Temp {
		$temp = strtolower($temp);
		if (!$temp) {
			throw new TempUnitException('Empty TEMP number is not allowed.');
		}
		if (!isset($this->map[$temp])) {
			throw new TempUnitException('TEMP unit ' . $temp . ' is not mapped.');
		}
		return $this->map[$temp];
	}

	/**
	 * Find newly created Unit.
	 *
	 * @throws TempUnitException
	 */
	public function find(Unit $unit): Temp {
		$id = $unit->Id()->Id();
		if (!isset($this->temp[$id])) {
			throw new TempUnitException('Unit ' . $unit->Id() . ' not found.');
		}
		return $this->map[$this->temp[$id]];
	}
}
