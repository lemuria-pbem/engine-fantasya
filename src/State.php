<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Campaign;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Factory\Model\Studies;
use Lemuria\Engine\Fantasya\Factory\Supply;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Engine\Fantasya\Realm\Fleet;
use Lemuria\Engine\Fantasya\Statistics\ContinentPopulation;
use Lemuria\Engine\Fantasya\Statistics\ContinentScenery;
use Lemuria\Engine\Fantasya\Turn\Options;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity\Luxury\Gem;
use Lemuria\Model\Fantasya\Continent;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

final class State implements Reassignment
{
	use BuilderTrait;

	private static ?self $instance = null;

	private readonly LemuriaTurn $turn;

	private ?Options $turnOptions = null;

	private readonly Casts $casts;

	/**
	 * @var array<int, Trade>
	 */
	private array $closedTrades = [];

	/**
	 * @var array<int, UnitMapper>
	 */
	private array $unitMapper = [];

	/**
	 * @var array<int, UnicumMapper>
	 */
	private array $unicumMapper = [];

	/**
	 * @var array<int, Availability>
	 */
	private array $availability = [];

	/**
	 * @var array<int, Allocation>
	 */
	private array $allocation = [];

	/**
	 * @var array<int, Commerce>
	 */
	private array $commerce = [];

	/**
	 * @var array<string, Supply>
	 */
	private array $supply = [];

	/**
	 * @var array<int, Intelligence>
	 */
	private array $intelligence = [];

	/**
	 * @var array<int, ResourcePool>
	 */
	private array $resourcePool = [];

	/**
	 * @var array<int, ActivityProtocol>
	 */
	private array $protocol = [];

	/**
	 * @var array<int, Workload>
	 */
	private array $workload = [];

	/**
	 * @var array<int, DirectionList>
	 */
	private array $travelRoute = [];

	/**
	 * @var array<int, Studies>
	 */
	private array $studies = [];

	/**
	 * @var array<int, Campaign>
	 */
	private array $campaigns = [];

	/**
	 * @var array<int, Fleet>
	 */
	private array $realmFleets = [];

	/**
	 * @var array<int, ContinentPopulation>
	 */
	private array $populations = [];

	/**$
	 * @var array<int, ContinentScenery>
	 */
	private array $sceneries = [];

	/**
	 * @var array<Behaviour>
	 */
	private array $monsters = [];

	public static function isInitialized(): bool {
		return self::$instance && self::$instance->turnOptions;
	}

	public static function getInstance(?LemuriaTurn $turn = null): State {
		if (!self::$instance) {
			self::$instance = new self();
			Lemuria::Catalog()->addReassignment(self::$instance);
		}
		if ($turn) {
			self::$instance->turn = $turn;
		}
		return self::$instance;
	}

	public function __construct() {
		$this->casts = new Casts();
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		$old = $oldId->Id();
		$new = $identifiable->Id()->Id();
		switch ($identifiable->Catalog()) {
			case Domain::Unit :
				$this->protocol[$new] = $this->protocol[$old];
				unset($this->protocol[$old]);
				if (isset($this->travelRoute[$old])) {
					$this->travelRoute[$new] = $this->travelRoute[$old];
					unset($this->travelRoute[$old]);
				}
				if (isset($this->workload[$old])) {
					$this->workload[$new] = $this->workload[$old];
					unset($this->workload[$old]);
				}
				break;
			case Domain::Location :
				$this->allocation[$new] = $this->allocation[$old];
				unset($this->allocation[$old]);
				$this->availability[$new] = $this->availability[$old];
				unset($this->availability[$new]);
				$this->campaigns[$new] = $this->campaigns[$old];
				unset($this->campaigns[$old]);
				$this->commerce[$new] = $this->commerce[$old];
				unset($this->commerce[$old]);
				$this->intelligence[$new] = $this->intelligence[$old];
				unset($this->intelligence[$old]);
				$this->reassignSupplies($old, $new);
				break;
			case Domain::Party :
				if (isset($this->unitMapper[$old])) {
					$this->unitMapper[$new] = $this->unitMapper[$old];
					unset($this->unitMapper[$old]);
				}
				break;
			case Domain::Realm :
				if (isset($this->realmFleets[$old])) {
					$this->realmFleets[$new] = $this->realmFleets[$old];
					unset($this->realmFleets[$old]);
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
				unset($this->resourcePool[self::resourcePoolId($identifiable)]);
				unset($this->protocol[$old]);
				unset($this->travelRoute[$old]);
				unset($this->workload[$old]);
				break;
			case Domain::Location :
				unset($this->allocation[$old]);
				unset($this->availability[$old]);
				unset($this->campaigns[$old]);
				unset($this->commerce[$old]);
				unset($this->intelligence[$old]);
				$this->unsetSupplies($old);
				break;
			case Domain::Party :
				unset($this->unitMapper[$old]);
				break;
			case Domain::Trade :
				/** @var Trade $identifiable */
				$this->closedTrades[$identifiable->Id()->Id()] = $identifiable;
				break;
			case Domain::Realm :
				unset($this->realmFleets[$old]);
				break;
			default :
		}
	}

	public function getCurrentPriority(): int {
		return $this->turn->getCurrentPriority();
	}

	public function getTurnOptions(): Options {
		if (!$this->turnOptions) {
			$this->turnOptions = new Options();
		}
		return $this->turnOptions;
	}

	public function getCasts(): Casts {
		return $this->casts;
	}

	public function getClosedTrades(): array {
		return $this->closedTrades;
	}

	/**
	 * Get a party's unit mapper.
	 */
	public function getUnitMapper(Party $party): UnitMapper {
		$id = $party->Id()->Id();
		if (!isset($this->unitMapper[$id])) {
			$this->unitMapper[$id] = new UnitMapper();
		}
		return $this->unitMapper[$id];
	}

	/**
	 * Get a party's unicum mapper.
	 */
	public function getUnicumMapper(Party $party): UnicumMapper {
		$id = $party->Id()->Id();
		if (!isset($this->unicumMapper[$id])) {
			$this->unicumMapper[$id] = new UnicumMapper();
		}
		return $this->unicumMapper[$id];
	}

	/**
	 * Get a region's available resources.
	 */
	public function getAvailability(Region $region): Availability {
		$id = $region->Id()->Id();
		if (!isset($this->availability[$id])) {
			$this->availability[$id] = new Availability($region);
		}
		return $this->availability[$id];
	}

	/**
	 * Get a region's allocation.
	 */
	public function getAllocation(Region $region): Allocation {
		$id = $region->Id()->Id();
		if (!isset($this->allocation[$id])) {
			$this->allocation[$id] = new Allocation($this->getAvailability($region));
		}
		return $this->allocation[$id];
	}

	/**
	 * Get a region's commerce.
	 */
	public function getCommerce(Region $region): Commerce {
		$id = $region->Id()->Id();
		if (!isset($this->commerce[$id])) {
			$this->commerce[$id] = new Commerce($region);
		}
		return $this->commerce[$id];
	}

	/**
	 * Get a region's luxury supply.
	 */
	public function getSupply(Region $region, ?Luxury $luxury = null): Supply {
		if (!$luxury) {
			$id = $region->Id()->Id() . '-';
			foreach ($this->supply as $key => $supply) {
				if (str_starts_with($key, $id)) {
					return $supply;
				}
			}
			$luxury = self::createCommodity(Gem::class);
		}

		$id = self::id($region, $luxury);
		if (!isset($this->supply[$id])) {
			$supply            = new Supply($region);
			$this->supply[$id] = $supply->setLuxury($luxury);
		}
		return $this->supply[$id];
	}

	/**
	 * Get a region's intelligence.
	 */
	public function getIntelligence(Region $region): Intelligence {
		$id = $region->Id()->Id();
		if (!isset($this->intelligence[$id])) {
			$this->intelligence[$id] = new Intelligence($region);
		}
		return $this->intelligence[$id];
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
	 * Get a unit's activity protocol.
	 */
	public function getProtocol(Unit $unit): ActivityProtocol {
		$id = $unit->Id()->Id();
		if (!isset($this->protocol[$id])) {
			$this->protocol[$id] = new ActivityProtocol($unit);
		}
		return $this->protocol[$id];
	}

	/**
	 * Get a unit's workload.
	 */
	public function getWorkload(Unit $unit): Workload {
		$id = $unit->Id()->Id();
		if (!isset($this->workload[$id])) {
			$this->workload[$id] = new Workload();
		}
		return $this->workload[$id];
	}

	/**
	 * Get the travel route of a unit.
	 */
	public function getTravelRoute(Unit $unit): ?DirectionList {
		$id = $unit->Id()->Id();
		return $this->travelRoute[$id] ?? null;
	}

	/**
	 * Get the studies of a unit.
	 */
	public function getStudies(Unit $unit): Studies {
		$id = $unit->Id()->Id();
		if (!isset($this->studies[$id])) {
			$this->studies[$id] = new Studies($unit);
		}
		return $this->studies[$id];
	}

	/**
	 * Get the battle of a region.
	 */
	public function getCampaign(Region $region): Campaign {
		$id = $region->Id()->Id();
		if (!isset($this->campaigns[$id])) {
			$this->campaigns[$id] = new Campaign($region);
		}
		return $this->campaigns[$id];
	}

	/**
	 * Get the fleet of a realm.
	 */
	public function getRealmFleet(Realm $realm): Fleet {
		$id = $realm->Id()->Id();
		if (!isset($this->realmFleets[$id])) {
			$this->realmFleets[$id] = new Fleet($realm);
		}
		return $this->realmFleets[$id];
	}

	/**
	 * Get the population of a continent.
	 */
	public function getPopulation(Continent $continent): ContinentPopulation {
		$id = $continent->Id()->Id();
		if (!isset($this->populations[$id])) {
			$this->populations[$id] = new ContinentPopulation($continent);
		}
		return $this->populations[$id];
	}

	/**
	 * Get the scenery of a continent.
	 */
	public function getScenery(Continent $continent): ContinentScenery {
		$id = $continent->Id()->Id();
		if (!isset($this->sceneries[$id])) {
			$this->sceneries[$id] = new ContinentScenery($continent);
		}
		return $this->sceneries[$id];
	}

	/**
	 * @return array<Commerce>
	 */
	public function getAllCommerces(): array {
		return array_values($this->commerce);
	}

	/**
	 * @return array<Supply>
	 */
	public function getAllSupplies(): array {
		return array_values($this->supply);
	}

	/**
	 * @return array<Behaviour>
	 */
	public function getAllMonsters(): array {
		return $this->monsters;
	}

	/**
	 * @return array<ActivityProtocol>
	 */
	public function getAllProtocols(): array {
		return $this->protocol;
	}

	public function setTurnOptions(Options $options): void {
		$this->turnOptions = $options;
	}

	public function setTravelRoute(Unit $unit, DirectionList $travelRoute): void {
		$id = $unit->Id()->Id();
		$this->travelRoute[$id] = $travelRoute;
	}

	public function addMonster(Behaviour $monster): void {
		$this->monsters[] = $monster;
	}

	public function injectIntoTurn(Action $action): void {
		$this->turn->inject($action);
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

	public function resetCampaign(Region $region): void {
		unset($this->campaigns[$region->Id()->Id()]);
	}

	private static function resourcePoolId(Unit $unit): string {
		return $unit->Party()->Id()->Id() . '-' . $unit->Region()->Id()->Id();
	}

	private static function id(Region $region, Luxury $luxury): string {
		return $region->Id()->Id() . '-' . getClass($luxury);
	}

	private function reassignSupplies(int $old, int $new): void {
		$sOld = $old . '-';
		$sNew = $new . '-';
		$i    = strlen($sOld);
		foreach (array_keys($this->supply) as $oldKey) {
			if (str_starts_with($oldKey, $sOld)) {
				$newKey                = $sNew . '-' . substr($oldKey, $i);
				$this->supply[$newKey] = $this->supply[$oldKey];
				unset($this->supply[$oldKey]);
			}
		}
	}

	private function unsetSupplies(int $old): void {
		$sOld = $old . '-';
		foreach (array_keys($this->supply) as $oldKey) {
			if (str_starts_with($oldKey, $sOld)) {
				unset($this->supply[$oldKey]);
			}
		}
	}
}
