<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Exception\ActionException;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Fantasya\Factory\CommandPriority;
use Lemuria\Engine\Fantasya\Factory\DefaultProgress;
use Lemuria\Engine\Move;
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
use Lemuria\Model\Newcomer;

/**
 * Main engine class.
 */
class LemuriaTurn implements Turn
{
	protected CommandPriority $priority;

	/**
	 * @var array(int=>array)
	 */
	protected array $queue = [];

	private State $state;

	private ?Progress $progress = null;

	/**
	 * Initialize turn.
	 */
	public function __construct() {
		$this->state    = State::getInstance();
		$this->priority = CommandPriority::getInstance();
		foreach (CommandPriority::ORDER as $priority) {
			$this->queue[$priority] = [];
		}
	}

	/**
	 * Get the Progress instance.
	 */
	public function Progress(): Progress {
		if (!$this->progress) {
			$this->progress = new DefaultProgress($this->state);
			Lemuria::Log()->debug('Using default Progress.', ['progress' => $this->progress]);
		}
		return $this->progress;
	}

	/**
	 * Override the Progress instance.
	 */
	public function setProgress(Progress $progress): Turn {
		$this->progress = $progress;
		Lemuria::Log()->debug('Overriding default Progress.', ['progress' => $progress]);
		return $this;
	}

	/**
	 * Add commands.
	 */
	public function add(Move $move): EntitySet {
		Lemuria::Log()->debug('Adding party move.', ['move' => $move]);
		$context = new Context($this->state);
		$factory = $context->Factory();
		$parser  = $context->Parser()->parse($move);
		$units   = new People();

		while ($parser->hasMore()) {
			$phrase = $parser->next();
			try {
				$command = $factory->create($phrase)->getDelegate();
				Lemuria::Log()->debug('New command: ' . $command, ['command' => $command]);
			} catch (UnknownCommandException $e) {
				Lemuria::Log()->error($e->getMessage(), ['exception' => $e]);
				continue;
			} catch (CommandParserException $e) {
				if ($parser->isSkip()) {
					Lemuria::Log()->warning('Skipping command: ' . $phrase);
					continue;
				}
				throw $e;
			}
			if ($parser->isSkip()) {
				Lemuria::Log()->warning('Skipping command: ' . $command);
				if ($command instanceof Immediate) {
					$command->skip();
				}
			} elseif ($command instanceof Immediate) {
				try {
					$command->execute();
				} catch (CommandException $e) {
					Lemuria::Log()->error($e->getMessage(), ['exception' => $e, 'command' => $command]);
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
		// TODO: Implement initiate() method.

		return $this;
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
		foreach ($this->Progress() as $event) {
			$this->enqueue($event);
			Lemuria::Log()->debug('Adding ' . $event . ' from Progress.', ['event' => $event]);
		}

		Lemuria::Log()->debug('Executing queued actions.', ['queues' => count($this->queue)]);
		foreach ($this->queue as $priority => $actions) {
			Lemuria::Log()->debug('Queue ' . $priority . ' has ' . count($actions) . ' actions.');
			foreach ($actions as $action /* @var Action $action */) {
				try {
					$action->prepare();
				} catch (ActionException $e) {
					Lemuria::Log()->error($e->getMessage(), ['stage' => 'prepare', 'action' => $action]);
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
				}
			}
		}
		Lemuria::Log()->debug('Queued actions executed.');
		return $this;
	}

	/**
	 * @throws LemuriaException
	 */
	public function addEvent(Event $event): Turn {
		$this->enqueue($event);
		Lemuria::Log()->debug('New event: ' . $event . '.', ['event' => $event]);
		return $this;
	}

	/**
	 * @throws LemuriaException
	 */
	public function addEffect(Effect $effect): Turn {
		$this->enqueue($effect);
		Lemuria::Log()->debug('New effect: ' . $effect . '.', ['effect' => $effect]);
		return $this;
	}

	protected function enqueue(Action $action): void {
		$priority                 = $this->priority->getPriority($action);
		$this->queue[$priority][] = $action;
	}

	private function substituteParty(Id $id): void {
		Lemuria::Log()->debug('Substitute Party ' . $id . '.');
		try {
			$party = Party::get($id);
		} catch (NotRegisteredException $e) {
			Lemuria::Log()->error($e->getMessage(), ['exception' => $e]);
			return;
		}

		$context = new Context($this->state);
		foreach ($party->People() as $unit /* @var Unit $unit */) {
			$command = $context->getProtocol($unit)->getDefaultCommand();
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
			$command = $context->getProtocol($unit)->getDefaultCommand();
			if ($command) {
				$this->enqueue($command);
				Lemuria::Log()->debug('Enqueue default command.', ['command' => $command]);
			} else {
				Lemuria::Log()->debug('No default command set.');
			}
		} catch (NotRegisteredException $e) {
			Lemuria::Log()->error($e->getMessage(), ['exception' => $e]);
		}
	}
}
