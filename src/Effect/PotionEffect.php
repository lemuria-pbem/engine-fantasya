<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Message\Unit\PotionEffectContinuesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\PotionEffectEndsMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Serializable;

final class PotionEffect extends AbstractUnitEffect
{
	use BuilderTrait;

	private Potion $potion;

	private int $count = 1;

	private int $weeks = 1;

	private bool $isFresh = false;

	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
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

	#[ArrayShape(['class' => "string", 'id' => "int", 'weeks' => "int", 'count' => "int", 'potion' => "string"])]
	#[Pure] public function serialize(): array {
		$data = parent::serialize();
		$data['potion'] = getClass($this->potion);
		$data['count']  = $this->count;
		$data['weeks']  = $this->weeks;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$potion = self::createCommodity($data['potion']);
		if ($potion instanceof Potion) {
			$this->potion = $potion;
			$this->count  = $data['count'];
			$this->weeks  = $data['weeks'];
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
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'potion', 'string');
		$this->validate($data, 'count', 'int');
		$this->validate($data, 'weeks', 'int');
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
