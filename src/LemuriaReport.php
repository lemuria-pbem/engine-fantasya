<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Exception\NotRegisteredException;
use Lemuria\Engine\Fantasya\Message\Exception\DuplicateMessageException;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Report;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Exception\DuplicateIdException;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Model\Reassignment;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

class LemuriaReport implements Reassignment, Report
{
	use SerializableTrait;

	private const string MESSAGES = 'messages';

	private const string REMOVED = 'removed';

	/**
	 * @var array<int, array>
	 */
	private array $report;

	/**
	 * @var array<LemuriaMessage>
	 */
	private array $message;

	/**
	 * @var array<int, array>
	 */
	private array $removed;

	private int $nextId;

	private bool $isLoaded = false;

	/**
	 * Init the report.
	 */
	public function __construct() {
		$this->clear();
		Lemuria::Catalog()->addReassignment($this);
	}

	/**
	 * Get the specified message.
	 *
	 * @throws NotRegisteredException
	 */
	public function get(Id $id): Message {
		$i = $id->Id() - 1;
		if (!isset($this->message[$i])) {
			throw new NotRegisteredException($id);
		}
		return $this->message[$i];
	}

	/**
	 * Get all messages of an entity.
	 *
	 * @return array<Message>
	 */
	public function getAll(Identifiable $entity): array {
		$messages = [];

		$namespace = $entity->Catalog();
		$id        = $entity->Id()->Id();
		if (isset($this->report[$namespace->value][$id])) {
			foreach ($this->report[$namespace->value][$id] as $i) {
				$messages[$i] = $this->message[$i];
			}
		}
		if ($namespace === Domain::Party && isset($this->removed[$id])) {
			foreach ($this->removed[$id] as $i) {
				$messages[$i] = $this->message[$i];
			}
		}

		ksort($messages);
		return array_values($messages);
	}

	/**
	 * Load message data into report.
	 */
	public function load(): static {
		if (!$this->isLoaded) {
			$report = Lemuria::Game()->getMessages();
			$this->validateSerializedData($report);
			foreach ($report[self::MESSAGES] as $data) {
				$message = new LemuriaMessage();
				$message->unserialize($data);
			}

			$this->removed  = $report[self::REMOVED];
			$this->isLoaded = true;
		}
		return $this;
	}

	/**
	 * Save game data from report.
	 */
	public function save(): static {
		$messages = [];
		foreach ($this->message as $message) {
			$messages[] = $message->serialize();
		}
		Lemuria::Game()->setMessages([self::MESSAGES => $messages, self::REMOVED => $this->removed]);
		return $this;
	}

	public function clear(): static {
		$this->report  = [];
		$this->message = [];
		$this->removed = [];
		$this->nextId  = 1;

		foreach (Domain::cases() as $namespace) {
			$this->report[$namespace->value] = [];
		}

		return $this;
	}

	/**
	 * Register a message.
	 *
	 * @throws DuplicateIdException
	 */
	public function register(Message $message): static {
		$namespace = $message->Report();
		$this->checkNamespace($namespace);
		$id = $message->Id()->Id() - 1;
		if (isset($this->message[$id])) {
			throw new DuplicateMessageException($message);
		}
		$entity = $message->Entity()->Id();

		$this->report[$namespace->value][$entity][] = $id;
		$this->message[$id] = $message;
		return $this;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		$namespace = $identifiable->Catalog()->value;
		$newId     = $identifiable->Id();
		$id        = $oldId->Id();
		if (isset($this->report[$namespace][$id])) {
			$messages =& $this->report[$namespace][$id];
			unset($this->report[$namespace][$id]);
			foreach ($messages as $id) {
				$this->message[$id]->reassign($newId);
			}

			$id = $newId->Id();
			if (isset($this->report[$namespace][$id])) {
				array_push($this->report[$namespace][$id], ...$messages);
				ksort($this->report[$namespace][$id]);
			} else {
				$this->report[$namespace][$id] = $messages;
			}
		}
	}

	public function remove(Identifiable $identifiable): void {
		if ($identifiable instanceof Unit) {
			$namespace = $identifiable->Catalog()->value;
			$id        = $identifiable->Id()->Id();
			if (isset($this->report[$namespace][$id])) {
				$party = $identifiable->Party()->Id()->Id();
				if (!isset($this->removed[$party])) {
					$this->removed[$party] = [];
				}
				foreach ($this->report[$namespace][$id] as $message) {
					$this->removed[$party][] = $message;
				}
			}
			return;
		}
		if ($identifiable instanceof Construction || $identifiable instanceof Vessel) {
			$namespace = $identifiable->Catalog()->value;
			$id        = $identifiable->Id()->Id();
			unset($this->report[$namespace][$id]);
		}
	}

	/**
	 * Reserve the next ID.
	 */
	public function nextId(): Id {
		return new Id($this->nextId++);
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array (string=>mixed) $data
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::MESSAGES, Validate::Array);
		$this->validate($data, self::REMOVED, Validate::Array);
	}

	/**
	 * Check if namespace is valid.
	 *
	 * @throws LemuriaException
	 */
	private function checkNamespace(Domain $namespace): void {
		if (!isset($this->report[$namespace->value])) {
			$bug = 'Namespace ' . $namespace->value . ' is not a valid report namespace.';
			throw new LemuriaException($bug, new \InvalidArgumentException());
		}
	}
}
