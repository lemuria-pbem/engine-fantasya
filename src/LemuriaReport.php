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
use Lemuria\Model\Catalog;
use Lemuria\Model\Exception\DuplicateIdException;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;
use Lemuria\SerializableTrait;

class LemuriaReport implements Reassignment, Report
{
	use SerializableTrait;

	/**
	 * @var array(int=>array)
	 */
	private array $report;

	/**
	 * @var LemuriaMessage[]
	 */
	private array $message;

	/**
	 * @var array(int=>array)
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
	 * @return Message[]
	 */
	public function getAll(Identifiable $entity): array {
		$messages = [];

		$namespace = $entity->Catalog();
		$id        = $entity->Id()->Id();
		if (isset($this->report[$namespace][$id])) {
			foreach ($this->report[$namespace][$id] as $i) {
				$messages[$i] = $this->message[$i];
			}
		}
		if ($namespace === Catalog::PARTIES && isset($this->removed[$id])) {
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
	public function load(): Report {
		if (!$this->isLoaded) {
			$report = Lemuria::Game()->getMessages();
			$this->validateSerializedData($report);
			foreach ($report['messages'] as $data) {
				$message = new LemuriaMessage();
				$message->unserialize($data);
			}

			$this->removed  = $report['removed'];
			$this->isLoaded = true;
		}
		return $this;
	}

	/**
	 * Save game data from report.
	 */
	public function save(): Report {
		$messages = [];
		foreach ($this->message as $message /* @var LemuriaMessage $message */) {
			$messages[] = $message->serialize();
		}
		Lemuria::Game()->setMessages(['messages' => $messages, 'removed' => $this->removed]);
		return $this;
	}

	public function clear(): Report {
		$this->report  = [];
		$this->message = [];
		$this->removed = [];
		$this->nextId  = 1;

		$reflection = new \ReflectionClass(Report::class);
		foreach ($reflection->getConstants() as $namespace) {
			if (!is_int($namespace)) {
				throw new LemuriaException('Expected integer report namespace.');
			}
			$this->report[$namespace] = [];
		}

		return $this;
	}

	/**
	 * Register a message.
	 *
	 * @throws DuplicateIdException
	 */
	public function register(Message $message): Report {
		$namespace = $message->Report();
		$this->checkNamespace($namespace);
		$id = $message->Id()->Id() - 1;
		if (isset($this->message[$id])) {
			throw new DuplicateMessageException($message);
		}
		$entity = $message->Entity()->Id();

		$this->report[$namespace][$entity][] = $id;
		$this->message[$id] = $message;
		return $this;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		$namespace = $identifiable->Catalog();
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
			$namespace = $identifiable->Catalog();
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
	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'messages', 'array');
		$this->validate($data, 'removed', 'array');
	}

	/**
	 * Check if namespace is valid.
	 *
	 * @throws LemuriaException
	 */
	private function checkNamespace(int $namespace): void {
		if (!isset($this->report[$namespace])) {
			$bug = 'Namespace ' . $namespace . ' is not a valid report namespace.';
			throw new LemuriaException($bug, new \InvalidArgumentException());
		}
	}
}
