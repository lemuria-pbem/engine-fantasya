<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Serializable;
use Lemuria\Validate;

final class Cartography extends AbstractRegionEffect
{
	private const PARTIES = 'parties';

	private Gathering $parties;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->parties = new Gathering();
	}

	public function Parties(): Gathering {
		return $this->parties;
	}

	public function serialize(): array {
		$data                = parent::serialize();
		$data[self::PARTIES] = $this->parties->serialize();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->parties->unserialize($data[self::PARTIES]);
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
