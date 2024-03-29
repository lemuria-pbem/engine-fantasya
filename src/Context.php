<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Combat\Besieger;
use Lemuria\Engine\Fantasya\Combat\Campaign;
use Lemuria\Engine\Fantasya\Exception\Command\PartyAlreadySetException;
use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Fantasya\Factory\CommandFactory;
use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Factory\Supply;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Engine\Fantasya\Realm\Fleet;
use Lemuria\Engine\Fantasya\Realm\Fund;
use Lemuria\Engine\Fantasya\Turn\Options;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;
use Lemuria\Model\World\Direction;

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
	 * @var array<int, Besieger>
	 */
	private array $sieges = [];

	/**
	 * @var array<int, Fund>
	 */
	private array $realmFunds = [];

	/**
	 * @var array<string, Blockade>
	 */
	private array $blockades = [];

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
		$old = $oldId->Id();
		$new = $identifiable->Id()->Id();
		switch ($identifiable->Catalog()) {
			case Domain::Unit :
				if (isset($this->calculus[$old])) {
					$this->calculus[$new] = $this->calculus[$old];
					unset($this->calculus[$old]);
				}
				break;
			case Domain::Realm :
				if (isset($this->realmFunds[$old])) {
					$this->realmFunds[$new] = $this->realmFunds[$old];
					unset($this->realmFunds[$old]);
				}
				break;
			default :
		}
	}

	public function remove(Identifiable $identifiable): void {
		$old = $identifiable->Id()->Id();
		switch ($identifiable->Catalog()) {
			case Domain::Unit :
				/** @var Unit $identifiable */
				unset($this->calculus[$old]);
				break;
			case Domain::Realm :
				unset($this->realmFunds[$old]);
				break;
			default :
		}
	}

	public function hasParty(): bool {
		return (bool)$this->party;
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
	 * Get a region's available resources.
	 */
	public function getAvailability(Region $region): Availability {
		return $this->state->getAvailability($region);
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
	 * Get a region's luxury supply.
	 */
	public function getSupply(Region $region, ?Luxury $luxury = null): Supply {
		return $this->state->getSupply($region, $luxury);
	}

	/**
	 * Get a resource pool.
	 */
	public function getResourcePool(Unit $unit): ResourcePool {
		return $this->state->getResourcePool($unit);
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
	 * Get the battle of a region.
	 */
	public function getCampaign(Region $region): Campaign {
		return $this->state->getCampaign($region);
	}

	/**
	 * Get the fleet of a realm.
	 */
	public function getRealmFleet(Realm $realm): Fleet {
		return $this->state->getRealmFleet($realm);
	}

	/**
	 * Get the fund of a realm.
	 */
	public function getRealmFund(Realm $realm): Fund {
		$id = $realm->Id()->Id();
		if (!isset($this->realmFunds[$id])) {
			$this->realmFunds[$id] = new Fund($realm, $this);
		}
		return $this->realmFunds[$id];
	}

	/**
	 * Get the blockade of a region and direction.
	 */
	public function getBlockade(Region $region, Direction $direction): Blockade {
		$id = $region->Id()->Id() . '-' . $direction->value;
		if (!isset($this->blockades[$id])) {
			$this->blockades[$id] = new Blockade();
		}
		return $this->blockades[$id];
	}

	/**
	 * Get the turn options.
	 */
	public function getTurnOptions(): Options {
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
			throw new PartyAlreadySetException();
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
		$this->state->resetResourcePools();
	}

	public function resetCampaign(Region $region): void {
		$this->state->resetCampaign($region);
	}

	public function getSiege(Construction $construction): Besieger {
		$id = $construction->Id()->Id();
		if (!isset($this->sieges)) {
			$this->sieges[$id] = new Besieger($construction);
		}
		return $this->sieges[$id];
	}
}
