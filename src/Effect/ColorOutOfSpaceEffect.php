<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Event\Population;
use Lemuria\Engine\Fantasya\Message\Party\Event\ColorOutOfSpaceDownInMessage;
use Lemuria\Engine\Fantasya\Message\Party\Event\ColorOutOfSpaceSummonInMessage;
use Lemuria\Engine\Fantasya\Message\Party\Event\ColorOutOfSpaceUpInMessage;
use Lemuria\Engine\Fantasya\Message\Party\Event\ColorOutOfSpaceWellInMessage;
use Lemuria\Engine\Fantasya\Message\Region\Event\ColorOutOfSpaceDownMessage;
use Lemuria\Engine\Fantasya\Message\Region\Event\ColorOutOfSpacePoisonMessage;
use Lemuria\Engine\Fantasya\Message\Region\Event\ColorOutOfSpaceSummonMessage;
use Lemuria\Engine\Fantasya\Message\Region\Event\ColorOutOfSpaceUpMessage;
use Lemuria\Engine\Fantasya\Message\Region\Event\ColorOutOfSpaceWellMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\World\Strategy\ShortestPath;
use Lemuria\Validate;

final class ColorOutOfSpaceEffect extends AbstractRegionEffect
{
	use BuilderTrait;

	private const string TARGET = 'target';

	private const string ROUNDS = 'rounds';

	private const string START = 'start';

	private const int RATE_BASE = 4;

	protected ?bool $isReassign = null;

	private Region $target;

	private int $rounds;

	private int $start;

	private ?float $poison = null;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function startTheRitual(): ColorOutOfSpaceEffect {
		$this->start = Lemuria::Calendar()->Round();
		$this->run();
		return $this;
	}

	public function serialize(): array {
		$data               = parent::serialize();
		$data[self::TARGET] = $this->target->Id()->Id();
		$data[self::ROUNDS] = $this->rounds;
		$data[self::START]  = $this->start;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->target = Region::get(new Id($data[self::TARGET]));
		$this->rounds = $data[self::ROUNDS];
		$this->start  = $data[self::START];
		return $this;
	}

	public function setTarget(Region $target): ColorOutOfSpaceEffect {
		$this->target = $target;
		return $this;
	}

	public function setRounds(int $rounds): ColorOutOfSpaceEffect {
		$this->rounds = $rounds;
		return $this;
	}

	protected function run(): void {
		$arrival = $this->getArrival();
		if ($arrival < 0) {
			$this->summonTheColorOutOfSpace();
		} elseif ($arrival === 0) {
			$this->finishTheRitual();
		} else {
			$this->poisonThePeasants();
		}
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::TARGET, Validate::Int);
		$this->validate($data, self::ROUNDS, Validate::Int);
		$this->validate($data, self::START, Validate::Int);
	}

	private function getArrival(): int {
		return Lemuria::Calendar()->Round() - $this->start - $this->rounds;
	}

	private function getPoisonRate(): float {
		$base    = self::RATE_BASE / 10;
		$i       = Lemuria::Calendar()->Round() - $this->start - $this->rounds - self::RATE_BASE;
		$current = abs($i) / 10;
		return 0.5 ** ($base - $current);
	}

	private function summonTheColorOutOfSpace(): void {
		$region    = $this->Region();
		$name      = $region->Name();
		$way       = Lemuria::World()->findPath($region, $this->target, ShortestPath::class)->getBest();
		$direction = Lemuria::World()->getDirection($way)->value;
		$this->message(ColorOutOfSpaceSummonMessage::class, $region)->p($direction);
		foreach ($this->state->getIntelligence($region)->getParties() as $party) {
			$this->message(ColorOutOfSpaceSummonInMessage::class, $party)->p($name)->p($direction, ColorOutOfSpaceDownInMessage::DIRECTION);
		}
		Lemuria::Log()->debug('The Color Out Of Space is summoned this week.');
	}

	private function finishTheRitual(): void {
		$name      = $this->target->Name();
		$landscape = $this->target->Landscape();
		if ($landscape instanceof Navigable) {
			$regions  = $this->getTargetRegions();
			$reported = new Gathering();
			foreach ($regions as $region) {
				$way       = Lemuria::World()->findPath($region, $this->target, ShortestPath::class)->getBest();
				$direction = Lemuria::World()->getDirection($way)->value;
				$this->message(ColorOutOfSpaceDownMessage::class, $region)->p($direction);
				foreach ($this->state->getIntelligence($region)->getParties() as $party) {
					if (!$reported->has($party->Id())) {
						$reported->add($party);
						$way       = Lemuria::World()->findPath($region, $this->target, ShortestPath::class)->getBest();
						$direction = Lemuria::World()->getDirection($way)->value;
						$this->message(ColorOutOfSpaceDownInMessage::class, $party)->p($region->Name())->p($direction, ColorOutOfSpaceDownInMessage::DIRECTION);
					}
				}
			}
		} else {
			$this->message(ColorOutOfSpaceWellMessage::class, $this->target);
			foreach ($this->state->getIntelligence($this->target)->getParties() as $party) {
				$this->message(ColorOutOfSpaceWellInMessage::class, $party)->p($name);
			}
		}
		Lemuria::Log()->debug('The Color Out Of Space has arrived in ' . $this->target . '!');
	}

	private function poisonThePeasants(): void {
		$regions = $this->getTargetRegions();
		$peasant = self::createCommodity(Peasant::class);
		$rate    = $this->getPoisonRate();
		if ($rate > 0.999) {
			$name      = $this->target->Name();
			$landscape = $this->target->Landscape();
			if ($landscape instanceof Navigable) {
				$regions = $this->getTargetRegions();
				foreach ($regions as $region) {
					$this->message(ColorOutOfSpaceUpMessage::class, $region);
					foreach ($this->state->getIntelligence($region)->getParties() as $party) {
						$this->message(ColorOutOfSpaceUpInMessage::class, $party)->p($name);
					}
				}
			} else {
				$this->message(ColorOutOfSpaceUpMessage::class, $this->target);
				foreach ($this->state->getIntelligence($this->target)->getParties() as $party) {
					$this->message(ColorOutOfSpaceUpInMessage::class, $party)->p($name);
				}
			}
			Lemuria::Score()->remove($this);
			Lemuria::Log()->debug('The Color Out Of Space has left ' . $this->target . '.');
			return;
		}

		foreach ($regions as $region) {
			$resources = $region->Resources();
			$peasants  = $resources[$peasant]->Count();
			$remaining = (int)floor($peasants * $rate);
			$deceased  = max(0, $peasants - $remaining);
			if ($deceased > 0) {
				$quantity = new Quantity($peasant, $deceased);
				$resources->remove($quantity);
				Population::addDeceasedPeasants($this->target, $deceased);
				$this->message(ColorOutOfSpacePoisonMessage::class, $region)->i($quantity);
				Lemuria::Log()->debug('The Color Out Of Space has poisoned ' . $peasants . ' peasants in ' . $region . '.');
			}
		}
	}

	private function getTargetRegions(): array {
		if ($this->target->Landscape() instanceof Navigable) {
			$regions = [];
			foreach (Lemuria::World()->getNeighbours($this->target) as $region) {
				/** @var Region $region */
				if ($region->Landscape() instanceof Navigable) {
					continue;
				}
				$regions[] = $region;
			}
		} else {
			$regions = [$this->target];
		}
		return $regions;
	}
}
