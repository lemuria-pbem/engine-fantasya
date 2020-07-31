<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Command\Create\Temp;
use Lemuria\Engine\Lemuria\Exception\CommandException;
use Lemuria\Engine\Lemuria\Command\Exception\TempUnitException;
use Lemuria\Model\Lemuria\Unit;

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
	 *
	 * @param string $tempNumber
	 * @return bool
	 */
	public function has(string $tempNumber): bool {
		return isset($this->map[$tempNumber]);
	}

	/**
	 * Add newly created Unit to mapper.
	 *
	 * @param Temp $command
	 * @return UnitMapper
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
	 * @param string $temp
	 * @return Temp
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
	 * @param Unit $unit
	 * @return Temp
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
