<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Serializable;
use Lemuria\Validate;

final class UnicumDisintegrate extends AbstractUnicumEffect
{
	private const ROUNDS = 'rounds';

	private int $rounds;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	public function Rounds(): int {
		return $this->rounds;
	}

	public function serialize(): array {
		$data               = parent::serialize();
		$data[self::ROUNDS] = $this->rounds;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->rounds = $data[self::ROUNDS];
		return $this;
	}

	public function setRounds(int $rounds): UnicumDisintegrate {
		$this->rounds = $rounds;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::ROUNDS, Validate::Int);
	}

	protected function run(): void {
		$unicum    = $this->Unicum();
		$collector = $unicum->Collector();
		if (!($collector instanceof Region)) {
			Lemuria::Score()->remove($this);
			Lemuria::Log()->debug('Disintegrate effect of ' . $unicum . ' has been removed.');
		} elseif ($this->rounds-- <= 0) {
			Lemuria::Score()->remove($this);
			$collector->Treasury()->remove($unicum);
			Lemuria::Catalog()->remove($unicum);
			Lemuria::Log()->debug('Unicum ' . $unicum . ' in ' . $collector . ' has been disintegrated.');
		}
	}
}
