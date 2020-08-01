<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Exception\CommandParserException;
use Lemuria\Engine\Lemuria\Factory\CommandFactory;
use Lemuria\Model\Lemuria\Party;
use Lemuria\Model\Lemuria\Region;
use Lemuria\Model\Lemuria\Unit;

/**
 * A context class available to all commands.
 */
final class Context
{
	private LemuriaTurn $turn;

	private Parser $parser;

	private CommandFactory $factory;

	private UnitMapper $mapper;

	private ?Party $party = null;

	private ?Unit $unit = null;

	/**
	 * @var array(int=>Calculus)
	 */
	private array $calculus = [];

	/**
	 * @var array(int=>Intelligence)
	 */
	private array $intelligence = [];

	/**
	 * @var array(int=>ActivityProtocol)
	 */
	private array $protocol = [];

	/**
	 * @var array(int=>Allocation)
	 */
	private array $allocation = [];

	/**
	 * @var array(int=>ResourcePool)
	 */
	private array $resourcePool = [];

	/**
	 * @param LemuriaTurn $turn
	 */
	public function __construct(LemuriaTurn $turn) {
		$this->turn    = $turn;
		$this->parser  = new Parser($this);
		$this->factory = new CommandFactory($this);
		$this->mapper  = new UnitMapper();
	}

	/**
	 * Get the Party whose commands are parsed.
	 *
	 * @return Party
	 * @throws CommandParserException
	 */
	public function Party(): Party {
		if (!$this->party) {
			throw new CommandParserException('Party has not been set.');
		}
		return $this->party;
	}

	/**
	 * Get current parsed Unit.
	 *
	 * @return Unit
	 * @throws CommandParserException
	 */
	public function Unit(): Unit {
		if (!$this->unit) {
			throw new CommandParserException('Unit has not been set.');
		}
		return $this->unit;
	}

	/**
	 * Get the command factory.
	 *
	 * @return CommandFactory
	 */
	public function Factory(): CommandFactory {
		return $this->factory;
	}

	/**
	 * @return Parser
	 */
	public function Parser(): Parser {
		return $this->parser;
	}

	/**
	 * Get the unit mapper.
	 *
	 * @return UnitMapper
	 */
	public function UnitMapper(): UnitMapper {
		return $this->mapper;
	}

	/**
	 * @return LemuriaTurn
	 */
	public function Turn(): LemuriaTurn {
		return $this->turn;
	}

	/**
	 * Get a unit's calculus.
	 *
	 * @param Unit $unit
	 * @return Calculus
	 */
	public function getCalculus(Unit $unit): Calculus {
		$id = $unit->Id()->Id();
		if (!isset($this->calculus[$id])) {
			$this->calculus[$id] = new Calculus($unit);
		}
		return $this->calculus[$id];
	}

	/**
	 * Get a unit's activity protocol.
	 *
	 * @param Unit $unit
	 * @return mixed
	 */
	public function getProtocol(Unit $unit): ActivityProtocol {
		$id = $unit->Id()->Id();
		if (!isset($this->protocol[$id])) {
			$this->protocol[$id] = new ActivityProtocol($unit);
		}
		return $this->protocol[$id];
	}

	/**
	 * Get a region's allocation.
	 *
	 * @param Region $region
	 * @return Allocation
	 */
	public function getAllocation(Region $region): Allocation {
		$id = $region->Id()->Id();
		if (!isset($this->allocation[$id])) {
			$this->allocation[$id] = new Allocation($region);
		}
		return $this->allocation[$id];
	}

	/**
	 * Get a resource pool.
	 *
	 * @param Unit $unit
	 * @return ResourcePool
	 */
	public function getResourcePool(Unit $unit): ResourcePool {
		$id = $unit->Party()->Id()->Id() . '-' . $unit->Region()->Id()->Id();
		if (!isset($this->resourcePool[$id])) {
			$this->resourcePool[$id] = new ResourcePool($unit);
		}
		return $this->resourcePool[$id];
	}

	/**
	 * Get a region's intelligence.
	 *
	 * @param Region $region
	 * @return Intelligence
	 */
	public function getIntelligence(Region $region): Intelligence {
		$id = $region->Id()->Id();
		if (!isset($this->intelligence[$id])) {
			$this->intelligence[$id] = new Intelligence($region);
		}
		return $this->intelligence[$id];
	}

	/**
	 * Set the Party whose commands are parsed.
	 *
	 * @param Party $party
	 * @return Context
	 * @throws CommandParserException
	 */
	public function setParty(Party $party): Context {
		if ($this->party) {
			throw new CommandParserException('Party has been set already.');
		}
		$this->party = $party;
		return $this;
	}

	/**
	 * Set current parsed Unit.
	 *
	 * @param Unit $unit
	 * @return Context
	 */
	public function setUnit(Unit $unit): Context {
		$this->unit = $unit;
		return $this;
	}
}
