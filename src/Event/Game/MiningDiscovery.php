<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Command\Create\RawMaterial;
use Lemuria\Engine\Fantasya\Message\Unit\Event\MiningDiscoveryMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Gold;
use Lemuria\Model\Fantasya\Commodity\Luxury\Gem;
use Lemuria\Model\Fantasya\Landscape\Glacier;
use Lemuria\Model\Fantasya\Landscape\Mountain;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Commodity\Iron;
use Lemuria\Model\Fantasya\Commodity\Stone;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;

/**
 * This event reveals precious discoveries when units produce stone or iron.
 *
 * It is used as single discovery event individually or as a single event from all RawMaterial commands that produce
 * these resources.
 */
final class MiningDiscovery extends AbstractEvent
{
	use BuilderTrait;
	use MessageTrait;
	use OptionsTrait;

	public final const UNIT = 'unit';

	public final const DISCOVERY = 'discovery';

	public final const AMOUNT = 'amount';

	private const MAX_CHANCE = 0.25;

	private const CHANCE = [
		Glacier::class => 0.12, Mountain::class => 0.04,
		Iron::class    => 0.2,  Stone::class    => 0.05
	];

	private const MAX_GEM = 25;

	private static ?self $instance = null;

	private Unit $unit;

	private Quantity $discovery;

	/**
	 * @var array<int, People>
	 */
	private array $people = [];

	/**
	 * @var array<int, float>
	 */
	private array $size = [];

	public static function getInstance(): self {
		if (!self::$instance) {
			self::$instance = new self(State::getInstance());
		}
		return self::$instance;
	}

	public static function addMiningDiscoveries(array &$events): void {
		Lemuria::Log()->debug('Adding MiningDiscovery.');
		$events[] = self::getInstance();
	}

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function setOptions(array $options): MiningDiscovery {
		$this->options = $options;
		return $this;
	}

	public function addMiner(RawMaterial $miner): void {
		$product = $miner->getCommodity();
		$chance  = self::CHANCE[$product::class] ?? 0.0;
		if ($chance > 0.0) {
			$unit      = $miner->Unit();
			$landscape = $unit->Region()->Landscape();
			if (isset(self::CHANCE[$landscape::class])) {
				$size = $chance * sqrt($unit->Size());
				if ($size > 0.0) {
					$this->addSize($unit, $size);
				}
			}
		}
	}

	protected function initialize(): void {
		if (empty($this->people)) {
			$this->unit = Unit::get($this->getIdOption(self::UNIT));
			$commodity  = self::createCommodity($this->getOption(self::DISCOVERY, 'string'));
			$amount     = 1;
			if ($this->hasOption(self::AMOUNT)) {
				$amount = $this->getOption(self::DISCOVERY, 'int');
			}
			$this->discovery = new Quantity($commodity, $amount);
		}
	}

	protected function run(): void {
		if (empty($this->people)) {
			$this->discover();
			return;
		}

		foreach ($this->people as $id => $people) {
			$region    = Region::get(new Id($id));
			$landscape = $region->Landscape();
			$chance    = min(self::MAX_CHANCE, $this->size[$id] * self::CHANCE[$landscape::class]);
			if (randChance($chance)) {
				$this->unit = $people->random();
				if ($landscape instanceof Glacier) {
					$this->discovery = new Quantity(self::createCommodity(Gold::class));
				} else {
					$amount          = (int)round($chance * self::MAX_GEM / self::MAX_CHANCE);
					$this->discovery = new Quantity(self::createCommodity(Gem::class), $amount);
				}
				$this->discover();
			}
		}
	}

	protected function discover(): void {
		$this->unit->Inventory()->add($this->discovery);
		$region    = $this->unit->Region();
		$landscape = $region->Landscape();
		$this->message(MiningDiscoveryMessage::class, $this->unit)->e($region)->s($landscape)->i($this->discovery);
	}

	private function addSize(Unit $unit, float $size): void {
		$region = $unit->Region()->Id()->Id();
		if (!isset($this->people[$region])) {
			$this->people[$region] = new People();
			$this->size[$region]   = 0.0;
		}
		$this->people[$region]->add($unit);
		$this->size[$region] += $size;
	}
}
