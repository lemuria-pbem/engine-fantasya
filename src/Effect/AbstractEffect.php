<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

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
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;

abstract class AbstractEffect implements Effect
{
	use ActionTrait;
	use SerializableTrait;

	protected Context $context;

	private Id $id;

	public function __construct(protected State $state, Priority $priority) {
		$this->setPriority($priority);
		$this->context = new Context($state);
	}

	public function needsAftercare(): bool {
		return false;
	}

	#[Pure] public function Id(): Id {
		return $this->id;
	}

	public function setId(Id $id): AbstractEffect {
		$this->id = $id;
		return $this;
	}

	/**
	 * Get action as string.
	 */
	#[Pure] public function __toString(): string {
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

	#[ArrayShape(['class' => "string", 'id' => "int"])]
	#[Pure] public function serialize(): array {
		return ['class' => getClass($this), 'id' => $this->id->Id()];
	}

	public function unserialize(array $data): Serializable {
		$this->validateSerializedData($data);
		if ($data['class'] !== getClass($this)) {
			throw new LemuriaException('Class name mismatch.', new UnserializeException());
		}
		$this->id = new Id($data['id']);
		return $this;
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'class', 'string');
		$this->validate($data, 'id', 'int');
	}
}
