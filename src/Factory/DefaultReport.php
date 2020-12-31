<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Exception\DuplicateMessageException;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Report;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Exception\DuplicateIdException;
use Lemuria\Model\Exception\NotRegisteredException;

class DefaultReport implements Report
{
	/**
	 * @var array(int=>array)
	 */
	private array $report = [];

	/**
	 * @var array(int=>LemuriaMessage)
	 */
	private array $message = [];

	private int $nextId = 1;

	private bool $isLoaded = false;

	/**
	 * Init the report.
	 */
	public function __construct() {
		$reflection = new \ReflectionClass(Report::class);
		foreach ($reflection->getConstants() as $namespace) {
			if (!is_int($namespace)) {
				throw new LemuriaException('Expected integer report namespace.');
			}
			$this->report[$namespace] = [];
		}
	}

	/**
	 * Get the specified message.
	 *
	 * @throws NotRegisteredException
	 */
	public function get(Id $id): Message {
		if (!isset($this->message[$id->Id()])) {
			throw new NotRegisteredException($id);
		}
		return $this->message[$id->Id()];
	}

	/**
	 * Get all messages of an entity.
	 */
	#[Pure] public function getAll(Identifiable $identifiable): array {
		$namespace = $identifiable->Catalog();
		$id        = $identifiable->Id()->Id();
		if (!isset($this->report[$namespace][$id])) {
			return [];
		}
		return $this->report[$namespace][$id];
	}

	/**
	 * Load message data into report.
	 */
	public function load(): Report {
		if (!$this->isLoaded) {
			foreach (Lemuria::Game()->getMessages() as $data) {
				$message = new LemuriaMessage();
				$message->unserialize($data);
			}
			$this->isLoaded = true;
		}
		return $this;
	}

	/**
	 * Save game data from report.
	 */
	public function save(): Report {
		$messages = [];
		foreach ($this->message as $id => $message /* @var LemuriaMessage $message */) {
			$messages[$id] = $message->serialize();
		}
		Lemuria::Game()->setMessages($messages);
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
		$id = $message->Id()->Id();
		if (isset($this->message[$id])) {
			throw new DuplicateMessageException($message);
		}
		$entity = $message->Entity()->Id();

		$this->report[$namespace][$entity][] = $message;
		$this->message[$id] = $message;
		if ($this->nextId === $id) {
			$this->searchNextId();
		}
		return $this;
	}

	/**
	 * Reserve the next ID.
	 */
	public function nextId(): Id {
		$id = new Id($this->nextId);
		$this->searchNextId();
		return $id;
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

	/**
	 * Search for next available ID.
	 */
	private function searchNextId(): void {
		do {
			$this->nextId++;
		} while (isset($this->message[$this->nextId]));
	}
}
