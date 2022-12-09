<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Treasury;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Reassignment;
use Lemuria\Serializable;
use Lemuria\Validate;

final class UnicumRead extends AbstractPartyEffect implements Reassignment
{
	private const TREASURY = 'treasury';

	private Treasury $treasury;

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
		$this->treasury = new Treasury();
		Lemuria::Catalog()->addReassignment($this);
	}

	public function Treasury(): Treasury {
		return $this->treasury;
	}

	public function serialize(): array {
		$data                 = parent::serialize();
		$data[self::TREASURY] = $this->treasury->serialize();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->treasury->unserialize($data[self::TREASURY]);
		return $this;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($this->treasury->has($oldId)) {
			$this->treasury->replace($oldId, $identifiable->Id());
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable instanceof Unicum && $this->treasury->has($identifiable->Id())) {
			$this->treasury->remove($identifiable);
		}
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::TREASURY, Validate::Array);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
