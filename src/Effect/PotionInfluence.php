<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\getClass;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Serializable;
use Lemuria\SingletonSet;
use Lemuria\Validate;

final class PotionInfluence extends AbstractRegionEffect
{
	use BuilderTrait;

	private const POTIONS = 'potions';

	private const POTION = 'potion';

	private const COUNT = 'count';

	private const WEEKS = 'weeks';

	private array $potions = [];

	private array $weeks = [];

	private bool $isFresh = false;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	public function IsFresh(): bool {
		return $this->isFresh;
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	public function serialize(): array {
		$data    = parent::serialize();
		$potions = [];
		foreach ($this->potions as $class => $count) {
			$weeks     = $this->weeks[$class];
			$potions[] = [self::POTION => $class, self::COUNT => $count, self::WEEKS => $weeks];
		}
		$data[self::POTIONS] = $potions;
		return $data;
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		foreach ($data[self::POTIONS] as $potionData) {
			$class  = $potionData[self::POTION];
			$potion = self::createCommodity($class);
			if ($potion instanceof Potion) {
				$this->potions[$class] = $potionData[self::COUNT];
				$this->weeks[$class]   = $potionData[self::WEEKS];
			} else {
				throw new LemuriaException('Unknown potion: ' . $potion);
			}
		}
		return $this;
	}

	public function getPotions(): SingletonSet {
		$potions = new SingletonSet();
		foreach (array_keys($this->potions) as $potion) {
			$potions->add(self::createCommodity($potion));
		}
		return $potions;
	}

	public function hasPotion(Potion $potion): bool {
		return $this->getCount($potion) > 0;
	}

	public function getCount(Potion $potion): int {
		return $this->potions[getClass($potion)] ?? 0;
	}

	public function addPotion(Quantity $quantity, int $weeks): PotionInfluence {
		/** @var Potion $potion */
		$potion = $quantity->Commodity();
		$class  = getClass($potion);
		$this->potions[$class] = $quantity->Count();
		$this->weeks[$class]   = $weeks;
		$this->isFresh = true;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::POTIONS, Validate::Array);
		foreach ($data[self::POTIONS] as $potion) {
			$this->validate($potion, self::POTION, Validate::String);
			$this->validate($potion, self::COUNT, Validate::Int);
			$this->validate($potion, self::WEEKS, Validate::Int);
		}
	}

	protected function run(): void {
		foreach (array_keys($this->weeks) as $potion) {
			$this->weeks[$potion]--;
			if ($this->weeks[$potion] <= 0) {
				unset($this->potions[$potion]);
				unset($this->weeks[$potion]);
			}
		}
		if (empty($this->potions)) {
			Lemuria::Score()->remove($this);
		}
	}
}
