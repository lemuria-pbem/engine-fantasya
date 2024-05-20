<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Validate;

final class FollowEffect extends AbstractUnitEffect
{
	private const string LEADER = 'leader';

	protected ?bool $isReassign = null;

	private Unit $leader;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function Leader(): Unit {
		return $this->leader;
	}

	public function serialize(): array {
		$data               = parent::serialize();
		$data[self::LEADER] = $this->leader->Id()->Id();
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->leader = Unit::get(new Id($data[self::LEADER]));
		return $this;
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable->Catalog() === Domain::Unit && $this->leader->Id()->Id() === $identifiable->Id()->Id()) {
			Lemuria::Score()->remove($this);
			Lemuria::Log()->debug($this->leader . ' has died, we cannot follow it anymore.');
		}
	}

	public function setLeader(Unit $leader): FollowEffect {
		$this->leader = $leader;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::LEADER, Validate::Int);
	}

	protected function run(): void {
		Lemuria::Log()->debug($this->Unit() . ' still follows ' . $this->leader . '.');
	}
}
