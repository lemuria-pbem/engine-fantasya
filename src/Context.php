<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Fantasya\Factory\CommandFactory;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

/**
 * A context class available to all commands.
 */
final class Context
{
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
	 * @var array(int=>ActivityProtocol)
	 */
	private array $protocol = [];

	/**
	 * @var array(int=>ResourcePool)
	 */
	private array $resourcePool = [];

	#[Pure] public function __construct(private State $state) {
		$this->parser  = new Parser($this);
		$this->factory = new CommandFactory($this);
		$this->mapper  = new UnitMapper();
	}

	/**
	 * Get the Party whose commands are parsed.
	 *
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
	 */
	#[Pure] public function Factory(): CommandFactory {
		return $this->factory;
	}

	#[Pure] public function Parser(): Parser {
		return $this->parser;
	}

	#[Pure] public function UnitMapper(): UnitMapper {
		return $this->mapper;
	}

	/**
	 * Get a unit's calculus.
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
	 */
	public function getProtocol(Unit $unit): ActivityProtocol {
		$id = $unit->Id()->Id();
		if (!isset($this->protocol[$id])) {
			$this->protocol[$id] = new ActivityProtocol($unit, $this);
		}
		return $this->protocol[$id];
	}

	/**
	 * Get a region's allocation.
	 */
	public function getAllocation(Region $region): Allocation {
		return $this->state->getAllocation($region);
	}

	/**
	 * Get a resource pool.
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
	 */
	public function getIntelligence(Region $region): Intelligence {
		return $this->state->getIntelligence($region);
	}

	/**
	 * Set the Party whose commands are parsed.
	 *
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
	 */
	public function setUnit(Unit $unit): Context {
		$this->unit = $unit;
		return $this;
	}
}
