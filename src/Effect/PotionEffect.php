<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Message\Unit\PotionEffectContinuesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\PotionEffectEndsMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Validate;

final class PotionEffect extends AbstractUnitEffect
{
	use BuilderTrait;

	private const string POTION = 'potion';

	private const string COUNT = 'count';

	private const string WEEKS = 'weeks';

	protected ?bool $isReassign = null;

	private Potion $potion;

	private int $count = 1;

	private int $weeks = 1;

	private bool $isFresh = false;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function needsAftercare(): bool {
		return true;
	}

	public function IsFresh(): bool {
		return $this->isFresh;
	}

	public function Potion(): Potion {
		return $this->potion;
	}

	public function Count(): int {
		return $this->count;
	}

	public function serialize(): array {
		$data               = parent::serialize();
		$data[self::POTION] = getClass($this->potion);
		$data[self::COUNT]  = $this->count;
		$data[self::WEEKS]  = $this->weeks;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$potion = self::createCommodity($data[self::POTION]);
		if ($potion instanceof Potion) {
			$this->potion = $potion;
			$this->count  = $data[self::COUNT];
			$this->weeks  = $data[self::WEEKS];
			return $this;
		}
		throw new LemuriaException('Unknown potion: ' . $potion);
	}

	public function setPotion(Potion $potion): PotionEffect {
		$this->potion = $potion;
		$this->isFresh = true;
		return $this;
	}

	public function setCount(int $count): PotionEffect {
		$this->count = $count;
		return $this;
	}

	public function setWeeks(int $weeks): PotionEffect {
		$this->weeks = $weeks;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::POTION, Validate::String);
		$this->validate($data, self::COUNT, Validate::Int);
		$this->validate($data, self::WEEKS, Validate::Int);
	}

	protected function run(): void {
		$this->weeks--;
		if ($this->weeks > 0) {
			$this->message(PotionEffectContinuesMessage::class, $this->Unit())->s($this->potion);
		} else {
			Lemuria::Score()->remove($this);
			$this->message(PotionEffectEndsMessage::class, $this->Unit())->s($this->potion);
		}
	}
}
