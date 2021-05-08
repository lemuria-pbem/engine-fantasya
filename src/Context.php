<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Fantasya\Factory\CommandFactory;
use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * A context class available to all commands.
 */
final class Context implements Reassignment
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

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($identifiable instanceof Unit) {
			$old = $oldId->Id();
			$new = $identifiable->Id()->Id();
			if (isset($this->calculus[$old])) {
				$this->calculus[$new] = $this->calculus[$old];
				unset($this->calculus[$old]);
			}
			if (isset($this->protocol[$old])) {
				$this->protocol[$new] = $this->protocol[$old];
				unset($this->protocol[$old]);
				$this->state->unsetProtocol($oldId);
				$this->state->setProtocol($this->protocol[$new]);
			}
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable instanceof Unit) {
			$id = $identifiable->Id()->Id();
			unset($this->calculus[$id]);
			$this->state->unsetProtocol($identifiable->Id());
			unset($this->protocol[$id]);
			unset($this->resourcePool[self::resourcePoolId($identifiable)]);
		}
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
			$protocol            = new ActivityProtocol($unit, $this);
			$this->protocol[$id] = $protocol;
			$this->state->setProtocol($protocol);
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
	 * Get a region's commerce.
	 */
	public function getCommerce(Region $region): Commerce {
		return $this->state->getCommerce($region);
	}

	/**
	 * Get a resource pool.
	 */
	public function getResourcePool(Unit $unit): ResourcePool {
		$id = self::resourcePoolId($unit);
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
	 * Get a unit's workload.
	 */
	public function getWorkload(Unit $unit): Workload {
		return $this->state->getWorkload($unit);
	}

	/**
	 * Get a unit's travel route.
	 */
	public function getTravelRoute(Unit $unit): DirectionList {
		$travelRoute = $this->state->getTravelRoute($unit);
		if (!$travelRoute) {
			$travelRoute = new DirectionList($this);
			$this->state->setTravelRoute($unit, $travelRoute);
		}
		return $travelRoute;
	}

	/**
	 * Get the turn options.
	 */
	public function getTurnOptions(): TurnOptions {
		return $this->state->getTurnOptions();
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

	private static function resourcePoolId(Unit $unit): string {
		return $unit->Party()->Id()->Id() . '-' . $unit->Region()->Id()->Id();
	}
}
