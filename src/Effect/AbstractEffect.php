<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\getClass;
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
use Lemuria\SerializableTrait;
use Lemuria\Validate;

abstract class AbstractEffect implements Effect, Reassignment
{
	use ActionTrait;
	use SerializableTrait;

	public final const string CLASS_KEY = 'class';

	private const string ID = 'id';

	protected Context $context;

	protected ?bool $isReassign = false;

	private Id $id;

	private bool $canExecute = true;

	public function __construct(protected State $state, Priority $priority) {
		$this->context = new Context($state);
		$this->setPriority($priority);
		if ($this->isReassign === false) {
			$this->addReassignment();
		}
	}

	public function Id(): Id {
		return $this->id;
	}

	public function isPrepared(): bool {
		return $this->preparation > 0 && $this->canExecute;
	}

	public function needsAftercare(): bool {
		return false;
	}

	public function supportsSimulation(): bool {
		return false;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($identifiable->Catalog() === $this->Catalog() && $this->id->Id() === $oldId->Id()) {
			$this->id = new Id($identifiable->Id()->Id());
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable->Catalog() === $this->Catalog() && $this->id->Id() === $identifiable->Id()->Id()) {
			$this->canExecute = false;
			Lemuria::Score()->remove($this);
			Lemuria::Log()->debug('Effect ' . $this . ' has been removed.');
		}
	}

	public function setId(Id $id): static {
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
	public function prepare(): static {
		Lemuria::Log()->debug('Preparing ' . $this . '.');
		$this->prepareAction();
		return $this;
	}

	/**
	 * Execute the Effect.
	 *
	 * @throws CommandException
	 */
	public function execute(): static {
		Lemuria::Log()->debug('Executing ' . $this . '.');
		$this->executeAction();
		return $this;
	}

	public function serialize(): array {
		return [self::CLASS_KEY => getClass($this), self::ID => $this->id->Id()];
	}

	public function unserialize(array $data): static {
		$this->validateSerializedData($data);
		if ($data[self::CLASS_KEY] !== getClass($this)) {
			throw new LemuriaException('Class name mismatch.', new UnserializeException());
		}
		$this->id = new Id($data[self::ID]);
		return $this->addReassignment();
	}

	public function addReassignment(): static {
		if (!$this->isReassign) {
			Lemuria::Catalog()->addReassignment($this);
			$this->isReassign = true;
		}
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::CLASS_KEY, Validate::String);
		$this->validate($data, self::ID, Validate::Int);
	}
}
