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
use Lemuria\Serializable;

final class Cartography extends AbstractRegionEffect
{
	private Gathering $parties;

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
		$this->parties = new Gathering();
	}

	public function Parties(): Gathering {
		return $this->parties;
	}

	#[ArrayShape(['class' => "string", 'id' => "int", 'parties' => "int[]"])]
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
