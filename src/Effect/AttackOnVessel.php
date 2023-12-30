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
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Validate;

final class AttackOnVessel extends AbstractUnitEffect
{
	private const VESSEL = 'vessel';

	private Id $vessel;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function Vessel(): Vessel {
		return Vessel::get($this->vessel);
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		parent::reassign($oldId, $identifiable);
		if ($identifiable->Catalog() === Domain::Vessel && $this->vessel->Id() === $oldId->Id()) {
			$this->vessel = $identifiable->Id();
		}
	}

	public function remove(Identifiable $identifiable): void {
		parent::remove($identifiable);
		if ($identifiable->Catalog() === Domain::Vessel && $identifiable->Id()->Id() === $this->vessel->Id()) {
			$this->canExecute = false;
			Lemuria::Score()->remove($this);
			Lemuria::Log()->debug('Effect ' . $this . ' has been removed.');
		}
	}

	public function serialize(): array {
		$data               = parent::serialize();
		$data[self::VESSEL] = $this->vessel->Id();
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->vessel = new Id($data[self::VESSEL]);
		return $this;
	}

	public function setVessel(Vessel $vessel): AttackOnVessel {
		$this->vessel = $vessel->Id();
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::VESSEL, Validate::Int);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
