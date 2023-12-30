<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Validate;

final class AttackOnUnits extends AbstractUnitEffect
{
	private const string UNITS = 'units';

	private People $units;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->units = new People();
	}

	public function Units(): People {
		return $this->units;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		parent::reassign($oldId, $identifiable);
		if ($identifiable->Catalog() === Domain::Unit && $this->units->has($oldId)) {
			$this->units->replace($oldId, $identifiable->Id());
		}
	}

	public function remove(Identifiable $identifiable): void {
		parent::remove($identifiable);
		if ($identifiable->Catalog() === Domain::Unit && $this->units->has($identifiable->Id())) {
			/** @var Unit $identifiable */
			$this->units->remove($identifiable);
		}
	}

	public function serialize(): array {
		$data              = parent::serialize();
		$data[self::UNITS] = $this->units->serialize();
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->units->unserialize($data[self::UNITS]);
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::UNITS, Validate::Array);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
