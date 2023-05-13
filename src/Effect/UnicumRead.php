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
use Lemuria\Serializable;
use Lemuria\Validate;

final class UnicumRead extends AbstractPartyEffect
{
	private const TREASURY = 'treasury';

	private Treasury $treasury;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->treasury = new Treasury();
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
		parent::reassign($oldId, $identifiable);
		if ($this->treasury->has($oldId)) {
			$this->treasury->replace($oldId, $identifiable->Id());
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
