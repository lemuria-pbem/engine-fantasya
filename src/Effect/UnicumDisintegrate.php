<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Serializable;

final class UnicumDisintegrate extends AbstractUnicumEffect
{
	private int $rounds;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	public function Rounds(): int {
		return $this->rounds;
	}

	#[ArrayShape(['class' => "string", 'id' => "int", 'rounds' => "int"])]
	#[Pure] public function serialize(): array {
		$data           = parent::serialize();
		$data['rounds'] = $this->rounds;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->rounds = $data['rounds'];
		return $this;
	}

	public function setRounds(int $rounds): UnicumDisintegrate {
		$this->rounds = $rounds;
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'rounds', 'int');
	}

	protected function run(): void {
		if ($this->rounds-- <= 0) {
			Lemuria::Score()->remove($this);
			$unicum    = $this->Unicum();
			$collector = $unicum->Collector();
			$collector->Treasury()->remove($unicum);
			Lemuria::Catalog()->remove($unicum);
			Lemuria::Log()->debug('Unicum ' . $unicum . ' in ' . $collector . ' has been disintegrated.');
		}
	}
}
