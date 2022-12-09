<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\ActionTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Exception\UnserializeException;
use Lemuria\Identifiable;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Reassignment;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

abstract class AbstractEffect implements Effect, Reassignment
{
	use ActionTrait;
	use SerializableTrait;

	private const ID = 'id';

	private const CLASS = 'class';

	protected Context $context;

	private Id $id;

	public function __construct(protected State $state, Priority $priority) {
		$this->context = new Context($state);
		$this->setPriority($priority);
	}

	public function Id(): Id {
		return $this->id;
	}

	public function needsAftercare(): bool {
		return false;
	}


	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($identifiable->Catalog() === $this->Catalog() && $this->id->Id() === $oldId->Id()) {
			$this->id = new Id($identifiable->Id()->Id());
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable->Catalog() === $this->Catalog() && $this->id->Id() === $identifiable->Id()->Id()) {
			Lemuria::Score()->remove($this);
		}
	}

	public function setId(Id $id): AbstractEffect {
		$this->id = $id;
		return $this;
	}

	/**
	 * Get action as string.
	 */
	public function __toString(): string {
		return 'Effect[' . $this->getPriority() . '] ' . getClass($this);
	}

	/**
	 * Prepare the execution of the Effect.
	 *
	 * @throws CommandException
	 */
	public function prepare(): Action {
		Lemuria::Log()->debug('Preparing ' . $this . '.');
		$this->prepareAction();
		return $this;
	}

	/**
	 * Execute the Effect.
	 *
	 * @throws CommandException
	 */
	public function execute(): Action {
		Lemuria::Log()->debug('Executing ' . $this . '.');
		$this->executeAction();
		return $this;
	}

	public function serialize(): array {
		return [self::CLASS => getClass($this), self::ID => $this->id->Id()];
	}

	public function unserialize(array $data): Serializable {
		$this->validateSerializedData($data);
		if ($data[self::CLASS] !== getClass($this)) {
			throw new LemuriaException('Class name mismatch.', new UnserializeException());
		}
		$this->id = new Id($data[self::ID]);
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::CLASS, Validate::String);
		$this->validate($data, self::ID, Validate::Int);
	}
}
