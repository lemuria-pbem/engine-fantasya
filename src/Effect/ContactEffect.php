<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\People;
use Lemuria\Serializable;

final class ContactEffect extends AbstractPartyEffect
{
	private People $from;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
		$this->from = new People();
	}

	public function From(): People {
		return $this->from;
	}

	public function serialize(): array {
		$data = parent::serialize();
		$data['from'] = $this->from->serialize();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->from->unserialize($data['from']);
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'from', 'array');
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
