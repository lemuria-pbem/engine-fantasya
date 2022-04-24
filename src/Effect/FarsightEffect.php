<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Serializable;

final class FarsightEffect extends AbstractRegionEffect
{
	private Gathering $parties;

	/**
	 * @var array(int=>int)
	 */
	private array $perception = [];

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
		$this->parties = new Gathering();
	}

	public function Parties(): Gathering {
		return $this->parties;
	}

	#[ArrayShape(['class' => "string", 'id' => "int", 'from' => "int[]"])]
	#[Pure] public function serialize(): array {
		$data            = parent::serialize();
		$data['parties'] = $this->parties->serialize();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->parties->unserialize($data['parties']);
		return $this;
	}

	#[Pure] public function getPerception(Party $party): int {
		$id = $party->Id()->Id();
		return $this->perception[$id] ?? 0;
	}

	public function addParty(Party $party, int $perception): FarsightEffect {
		$this->parties->add($party);
		$id = $party->Id()->Id();
		if (isset($this->perception[$id])) {
			$this->perception[$id] = max($this->perception[$id], $perception);
		} else {
			$this->perception[$id] = $perception;
		}
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'parties', 'array');
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
