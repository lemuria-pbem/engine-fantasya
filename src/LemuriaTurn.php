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
use Lemuria\Exception\LemuriaException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Lemuria;

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
	public function add(Move $move): Turn {
		Lemuria::Log()->debug('Adding party move.', ['move' => $move]);
		$context = new Context($this->state);
		$factory = $context->Factory();
		$parser  = $context->Parser()->parse($move);
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
			}
		}
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
	 * Add action to the right queue.
	 *
	 * @throws LemuriaException
	 */
	protected function enqueue(Action $action): void {
		$priority                 = $this->priority->getPriority($action);
		$this->queue[$priority][] = $action;
	}
}
