<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\People;
use Lemuria\Serializable;
use Lemuria\Validate;

final class ContactEffect extends AbstractPartyEffect
{
	private const FROM = 'from';

	private People $from;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->from = new People();
	}

	public function From(): People {
		return $this->from;
	}

	public function serialize(): array {
		$data             = parent::serialize();
		$data[self::FROM] = $this->from->serialize();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->from->unserialize($data[self::FROM]);
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::FROM, Validate::Array);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
