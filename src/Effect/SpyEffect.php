<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Command\Spy;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Validate;

final class SpyEffect extends AbstractPartyEffect
{
	private const string TARGETS = 'targets';

	/**
	 * @var array<int, int>
	 */
	private array $targets = [];

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function Targets(): array {
		return $this->targets;
	}

	public function serialize(): array {
		$data                = parent::serialize();
		$data[self::TARGETS] = $this->targets;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->targets = $data[self::TARGETS];
		return $this;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		parent::reassign($oldId, $identifiable);
		if ($identifiable instanceof Unit) {
			$id = $oldId->Id();
			if (isset($this->targets[$id])) {
				$this->targets[$identifiable->Id()->Id()] = $this->targets[$id];
				unset($this->targets[$id]);
			}
		}
	}

	public function remove(Identifiable $identifiable): void {
		parent::remove($identifiable);
		if ($identifiable instanceof Unit) {
			$id = $identifiable->Id()->Id();
			unset($this->targets[$id]);
		}
	}

	public function isRevealed(Unit $unit): bool {
		$id    = $unit->Id()->Id();
		$level = $this->targets[$id] ?? 0;
		return $level >= Spy::LEVEL_REVEAL_DISGUISE;
	}

	public function addTarget(Unit $target, int $level): SpyEffect {
		$id = $target->Id()->Id();
		if (isset($this->targets[$id])) {
			$level = max($this->targets[$id], $level);
		}
		$this->targets[$id] = $level;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::TARGETS, Validate::Array);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
