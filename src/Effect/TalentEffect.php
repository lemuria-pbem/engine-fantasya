<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Knowledge;
use Lemuria\Serializable;

final class TalentEffect extends AbstractUnitEffect
{
	private Knowledge $modifications;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
		$this->modifications = new Knowledge();
	}

	public function Modifications(): Knowledge {
		return $this->modifications;
	}

	#[ArrayShape(['class' => "string", 'id' => "int", 'modifications' => "array"])]
	#[Pure] public function serialize(): array {
		$data = parent::serialize();
		$data['modifications'] = $this->modifications->serialize();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->modifications->unserialize($data['modifications']);
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'modifications', 'array');
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
