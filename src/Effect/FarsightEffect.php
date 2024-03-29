<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Validate;

final class FarsightEffect extends AbstractRegionEffect
{
	private const string PARTIES = 'parties';

	protected ?bool $isReassign = null;

	private Gathering $parties;

	/**
	 * @var array<int, int>
	 */
	private array $perception = [];

	public function __construct(State $state) {
		$this->parties = new Gathering();
		parent::__construct($state, Priority::Before);
	}

	public function Parties(): Gathering {
		return $this->parties;
	}

	public function serialize(): array {
		$data                = parent::serialize();
		$data[self::PARTIES] = $this->parties->serialize();
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->parties->unserialize($data[self::PARTIES]);
		return $this;
	}

	public function addReassignment(): static {
		if (!$this->isReassign) {
			parent::addReassignment();
			$this->parties->addReassignment();
		}
		return $this;
	}

	public function getPerception(Party $party): int {
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
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::PARTIES, Validate::Array);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
