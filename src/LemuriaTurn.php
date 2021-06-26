<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Exception\EngineException;
use Lemuria\Engine\Fantasya\Command\Initiate;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Event\DelegatedEvent;
use Lemuria\Engine\Fantasya\Exception\ActionException;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Factory\BuilderTrait;
use Lemuria\Engine\Fantasya\Factory\CommandPriority;
use Lemuria\Engine\Fantasya\Factory\Model\LemuriaNewcomer;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\NoMoveMessage;
use Lemuria\Engine\Fantasya\Message\Party\PartyExceptionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnitExceptionMessage;
use Lemuria\Engine\Move;
use Lemuria\Engine\Newcomer;
use Lemuria\Engine\Score;
use Lemuria\Engine\Turn;
use Lemuria\EntitySet;
use Lemuria\Exception\LemuriaException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Version\VersionFinder;
use Lemuria\Version\VersionTag;

/**
 * Main engine class.
 */
class LemuriaTurn implements Turn
{
	use BuilderTrait;

	protected CommandPriority $priority;

	/**
	 * @var array(int=>array)
	 */
	protected array $queue = [];

	private State $state;

	/**
	 * Initialize turn.
	 */
	public function __construct(?TurnOptions $options = null) {
		$this->state = State::getInstance();
		if ($options) {
			$this->state->setTurnOptions($options);
		}
		$this->priority = CommandPriority::getInstance();
		foreach (CommandPriority::ORDER as $priority) {
			$this->queue[$priority] = [];
		}
		Lemuria::Report()->clear();
	}

	/**
	 * Add commands.
	 */
	public function add(Move $move): EntitySet {
		Lemuria::Log()->debug('Adding party move.', ['move' => $move]);
		$context = new Context($this->state);
		Lemuria::Catalog()->addReassignment($context);
		$factory = $context->Factory();
		$parser  = $context->Parser()->parse($move);
		$units   = new People();

		while ($parser->hasMore()) {
			$phrase = $parser->next();
			try {
				$command = $factory->create($phrase)->getDelegate();
				Lemuria::Log()->debug('New command: ' . $command, ['command' => $command]);
			} catch (UnknownCommandException|UnknownItemException $e) {
				Lemuria::Log()->error($e->getMessage(), ['exception' => $e]);
				$this->addExceptionMessage($e, $context);
				if ($this->throwExceptions()) {
					throw $e;
				}
				continue;
			} catch (CommandParserException $e) {
				if ($parser->isSkip()) {
					Lemuria::Log()->error('Skipping command: ' . $phrase);
					$this->addSkipMessage($phrase, $context);
					continue;
				}
				throw $e;
			}
			if ($parser->isSkip()) {
				Lemuria::Log()->error('Skipping command: ' . $command);
				if ($command instanceof Immediate) {
					$command->skip();
					$this->addSkipMessage($command, $context);
				}
			} elseif ($command instanceof Immediate) {
				try {
					$command->execute();
				} catch (CommandException $e) {
					Lemuria::Log()->error($e->getMessage(), ['exception' => $e, 'command' => $command]);
					$this->addExceptionMessage($e, $context);
					if ($this->throwExceptions()) {
						throw $e;
					}
				}
			} else {
				$this->enqueue($command);
				if ($command instanceof Activity) {
					$units->add($context->Unit());
				}
			}
		}

		return $units;
	}

	/**
	 * Bring a new party into the game.
	 */
	public function initiate(Newcomer $newcomer): Turn {
		if ($newcomer instanceof LemuriaNewcomer) {
			$command = new Initiate($newcomer);
			Lemuria::Log()->debug('New command: ' . $command, ['command' => $command]);
			$this->enqueue($command);
			return $this;
		}
		throw new LemuriaException('LemuriaNewcomer expected.');
	}

	/**
	 * Add default commands of given entity.
	 */
	public function substitute(Identifiable $entity): Turn {
		switch ($entity->Catalog()) {
			case Catalog::PARTIES :
				$this->substituteParty($entity->Id());
				break;
			case Catalog::UNITS :
				$this->substituteUnit($entity->Id());
				break;
			default :
				throw new LemuriaException('Cannot substitute entity of catalog ' . $entity->Catalog() . '.');
		}
		return $this;
	}

	/**
	 * Evaluate the whole turn.
	 */
	public function evaluate(): Turn {
		Lemuria::Orders()->clear();
		Lemuria::Log()->debug('Executing queued actions.', ['queues' => count($this->queue)]);
		foreach ($this->queue as $priority => $actions) {
			Lemuria::Log()->debug('Queue ' . $priority . ' has ' . count($actions) . ' actions.');
			foreach ($actions as $action /* @var Action $action */) {
				try {
					$action->prepare();
				} catch (ActionException $e) {
					Lemuria::Log()->error($e->getMessage(), ['stage' => 'prepare', 'action' => $action]);
					$this->addActionException($e, $action);
					if ($this->throwExceptions()) {
						throw $e;
					}
				}
			}
			foreach ($actions as $action /* @var Action $action */) {
				try {
					if ($action->isPrepared()) {
						$action->execute();
					} else {
						Lemuria::Log()->debug('Unprepared action skipped.', ['action' => $action]);
					}
				} catch (ActionException $e) {
					Lemuria::Log()->error($e->getMessage(), ['stage' => 'execute', 'action' => $action]);
					$this->addActionException($e, $action);
					if ($this->throwExceptions()) {
						throw $e;
					}
				}
			}
		}
		Lemuria::Log()->debug('Queued actions executed.');
		return $this;
	}

	/**
	 * Add events from default progress,
	 */
	public function addProgress(Progress $progress): LemuriaTurn {
		Lemuria::Log()->debug('Adding events from progress.', ['progress' => $progress]);
		foreach ($progress as $event) {
			$this->addEvent($event);
		}
		return $this;
	}

	/**
	 * Add effects from Score.
	 */
	public function addScore(Score $score): LemuriaTurn {
		foreach ($score as $effect /* @var Effect $effect */) {
			$this->addEffect($effect);
		}
		return $this;
	}

	public function getVersion(): VersionTag {
		$versionFinder = new VersionFinder(__DIR__ . '/..');
		return $versionFinder->get();
	}

	protected function enqueue(Action $action): void {
		$priority                 = $this->priority->getPriority($action);
		$this->queue[$priority][] = $action;
	}

	protected function addEvent(Event $event): Turn {
		if ($event instanceof DelegatedEvent) {
			Lemuria::Log()->debug('New delegated event: ' . $event . '.', ['event' => $event]);
			foreach ($event->getDelegates() as $delegate) {
				$this->enqueue($delegate);
				Lemuria::Log()->debug('New event from delegate: ' . $delegate . '.', ['event' => $delegate]);
			}
		} else {
			$this->enqueue($event);
			Lemuria::Log()->debug('New event: ' . $event . '.', ['event' => $event]);
		}
		return $this;
	}

	protected function addEffect(Effect $effect): Turn {
		$this->enqueue($effect);
		Lemuria::Log()->debug('New effect: ' . $effect . '.', ['effect' => $effect]);
		return $this;
	}

	private function throwExceptions(): bool {
		return $this->state->getTurnOptions()->ThrowExceptions();
	}

	private function substituteParty(Id $id): void {
		Lemuria::Log()->debug('Substitute Party ' . $id . '.');
		try {
			$party = Party::get($id);
		} catch (NotRegisteredException $e) {
			Lemuria::Log()->critical($e->getMessage(), ['exception' => $e]);
			if ($this->throwExceptions()) {
				throw $e;
			}
			return;
		}

		$id          = Lemuria::Report()->nextId();
		$message     = new LemuriaMessage();
		$messageType = self::createMessageType(NoMoveMessage::class);
		$message->setAssignee($party->Id())->setType($messageType)->setId($id);

		$context = new Context($this->state);
		Lemuria::Catalog()->addReassignment($context);
		$context->setParty($party);
		foreach ($party->People() as $unit /* @var Unit $unit */) {
			$command = $context->setUnit($unit)->getProtocol($unit)->getDefaultCommand();
			if ($command) {
				$this->enqueue($command);
				Lemuria::Log()->debug('Enqueue default command.', ['unit' => $unit->Id(), 'command' => $command]);
			} else {
				Lemuria::Log()->debug('No default command for unit ' . $unit->Id() . '.');
			}
		}
	}

	private function substituteUnit(Id $id): void {
		Lemuria::Log()->debug('Substitute Unit ' . $id . '.');
		try {
			$unit    = Unit::get($id);
			$context = new Context($this->state);
			Lemuria::Catalog()->addReassignment($context);
			$command = $context->setParty($unit->Party())->setUnit($unit)->getProtocol($unit)->getDefaultCommand();
			if ($command) {
				$this->enqueue($command);
				Lemuria::Log()->debug('Enqueue default command.', ['command' => $command]);
			} else {
				Lemuria::Log()->debug('No default command set.');
			}
		} catch (NotRegisteredException $e) {
			Lemuria::Log()->critical($e->getMessage(), ['exception' => $e]);
			if ($this->throwExceptions()) {
				throw $e;
			}
		}
	}

	private function addSkipMessage(Command|Phrase $command, Context $context): void {
		try {
			$party = $context->Party();
		} catch (CommandParserException) {
			return;
		}

		$id          = Lemuria::Report()->nextId();
		$message     = new LemuriaMessage();
		$messageType = self::createMessageType(PartyExceptionMessage::class);
		$parameter   = 'Skipping command ' . $command . '.';
		$message->setAssignee($party->Id())->setType($messageType)->p($parameter)->setId($id);
	}

	private function addExceptionMessage(EngineException $exception, Context $context): void {
		try {
			$party = $context->Party();
		} catch (CommandParserException) {
			return;
		}

		$id          = Lemuria::Report()->nextId();
		$message     = new LemuriaMessage();
		$messageType = self::createMessageType(PartyExceptionMessage::class);
		$message->setAssignee($party->Id())->setType($messageType)->p($exception->getMessage())->setId($id);
	}

	private function addActionException(EngineException $exception, Action $action): void {
		if ($action instanceof UnitCommand) {
			$id          = Lemuria::Report()->nextId();
			$message     = new LemuriaMessage();
			$messageType = self::createMessageType(PartyExceptionMessage::class);
			$message->setAssignee($action->Unit()->Party()->Id())->setType($messageType)->p((string)$action)->setId($id);

			$id          = Lemuria::Report()->nextId();
			$message     = new LemuriaMessage();
			$messageType = self::createMessageType(UnitExceptionMessage::class);
			$message->setAssignee($action->Unit()->Id())->setType($messageType);
			$message->p($exception->getMessage())->p((string)$action->Phrase(), UnitExceptionMessage::ACTION)->setId($id);
		}
	}
}
