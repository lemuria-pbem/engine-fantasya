<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Message\Region\UnemploymentMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Serializable;

final class Unemployment extends AbstractRegionEffect
{
	private int $peasants = 0;

	public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
	}

	public function Peasants(): int {
		return $this->peasants;
	}

	public function setPeasants(int $peasants): Unemployment {
		$this->peasants = $peasants;
		return $this;
	}

	#[ArrayShape(['class' => "string", 'id' => "int", 'peasants' => "int"])]
	#[Pure] public function serialize(): array {
		$data = parent::serialize();
		$data['peasants'] = $this->peasants;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->peasants = $data['peasants'];
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'peasants', 'int');
	}

	protected function run(): void {
		$region = $this->Region();
		if ($region->Landscape()->Workplaces() > 0) {
			$this->message(UnemploymentMessage::class, $region)->p($this->peasants);
		}
	}
}
