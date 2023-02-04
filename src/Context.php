<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Combat\Besieger;
use Lemuria\Engine\Fantasya\Combat\Campaign;
use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Fantasya\Factory\CommandFactory;
use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * A context class available to all commands.
 */
final class Context implements Reassignment
{
	private readonly Parser $parser;

	private readonly CommandFactory $factory;

	private ?Party $party = null;

	private ?Unit $unit = null;

	/**
	 * @var array<int, Calculus>
	 */
	private array $calculus = [];

	/**
	 * @var array<int, ResourcePool>
	 */
	private array $resourcePool = [];

	/**
	 * @var array<int, Besieger>
	 */
	private array $sieges = [];

	public function __construct(private readonly State $state) {
		$this->parser  = new Parser();
		$this->factory = new CommandFactory($this);
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
	public function Factory(): CommandFactory {
		return $this->factory;
	}

	public function Parser(): Parser {
		return $this->parser;
	}

	public function UnitMapper(): UnitMapper {
		return $this->state->getUnitMapper($this->Party());
	}

	public function UnicumMapper(): UnicumMapper {
		return $this->state->getUnicumMapper($this->Party());
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($identifiable instanceof Unit) {
			$old = $oldId->Id();
			$new = $identifiable->Id()->Id();
			if (isset($this->calculus[$old])) {
				$this->calculus[$new] = $this->calculus[$old];
				unset($this->calculus[$old]);
			}
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable instanceof Unit) {
			$id = $identifiable->Id()->Id();
			unset($this->calculus[$id]);
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
		return $this->state->getProtocol($unit);
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
			$this->resourcePool[$id] = new ResourcePool($unit, $this);
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
	 * Check if a unit is travelling.
	 */
	public function isTravelling(Unit $unit): bool {
		return $this->state->isTravelling && $this->state->getTravelRoute($unit) !== null;
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
	 * Get the battle of a region.
	 */
	public function getCampaign(Region $region): Campaign {
		return $this->state->getCampaign($region);
	}

	/**
	 * Get the turn options.
	 */
	public function getTurnOptions(): TurnOptions {
		return $this->state->getTurnOptions();
	}

	/**
	 * Get the casts queue.
	 */
	public function getCasts(): Casts {
		return $this->state->getCasts();
	}

	/**
	 * Get the closed trades.
	 *
	 * @return array<int, Trade>
	 */
	public function getClosedTrades(): array {
		return $this->state->getClosedTrades();
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

	/**
	 * Clears all existing resource pools when units have moved.
	 */
	public function resetResourcePools(): void {
		$n = count($this->resourcePool);
		if ($n > 0) {
			$this->resourcePool = [];
			Lemuria::Log()->debug('Clearing ' . $n . ' resource pools.');
		}
	}

	public function getSiege(Construction $construction): Besieger {
		$id = $construction->Id()->Id();
		if (!isset($this->sieges)) {
			$this->sieges[$id] = new Besieger($construction);
		}
		return $this->sieges[$id];
	}

	private static function resourcePoolId(Unit $unit): string {
		return $unit->Party()->Id()->Id() . '-' . $unit->Region()->Id()->Id();
	}
}
