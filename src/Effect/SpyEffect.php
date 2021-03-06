<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Command\Spy;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;
use Lemuria\Serializable;

final class SpyEffect extends AbstractPartyEffect implements Reassignment
{
	/**
	 * @var array(int=>int)
	 */
	private array $targets = [];

	public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
		Lemuria::Catalog()->addReassignment($this);
	}

	public function Targets(): array {
		return $this->targets;
	}

	#[ArrayShape(['class' => "string", 'id' => "int", 'targets' => "array"])]
	#[Pure] public function serialize(): array {
		$data            = parent::serialize();
		$data['targets'] = $this->targets;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->targets = $data['targets'];
		return $this;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($identifiable instanceof Unit) {
			$id = $oldId->Id();
			if (isset($this->targets[$id])) {
				$this->targets[$identifiable->Id()->Id()] = $this->targets[$id];
				unset($this->targets[$id]);
			}
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable instanceof Unit) {
			$id = $identifiable->Id()->Id();
			unset($this->targets[$id]);
		}
	}

	#[Pure] public function isRevealed(Unit $unit): bool {
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
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'targets', 'array');
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
