<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Market\Deal;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Validate;

final class UnicumOffer extends AbstractUnicumEffect
{
	use MessageTrait;

	private const string OFFERS = 'offers';

	/**
	 * @var array<int, Deal>
	 */
	private array $offers = [];

	/**
	 * @var array<int, bool>
	 */
	private array $isNew = [];

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}


	public function serialize(): array {
		$data   = parent::serialize();
		$offers = [];
		foreach ($this->offers as $id => $deal) {
			$offers[$id] = $deal->serialize();
		}
		$data[self::OFFERS] = $offers;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->offers = [];
		foreach ($data[self::OFFERS] as $id => $dealData) {
			$deal              = new Deal();
			$this->offers[$id] = $deal->unserialize($dealData);
		}
		return $this;
	}

	public function getOffer(Unit $buyer): ?Deal {
		return $this->offers[$buyer->Id()->Id()] ?? null;
	}

	public function addOffer(Unit $buyer, Deal $deal): UnicumOffer {
		$id                = $buyer->Id()->Id();
		$this->offers[$id] = $deal;
		$this->isNew[$id]  = true;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::OFFERS, Validate::Array);
	}

	protected function run(): void {
		foreach (array_keys($this->offers) as $id) {
			if (isset($this->isNew[$id])) {
				continue;
			}
			unset($this->offers[$id]);
			unset($this->isNew[$id]);
		}
		if (empty($this->offers)) {
			Lemuria::Score()->remove($this);
		}
	}
}
