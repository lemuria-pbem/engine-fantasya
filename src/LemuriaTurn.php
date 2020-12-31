<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Exception\ActionException;
use Lemuria\Engine\Lemuria\Exception\CommandException;
use Lemuria\Engine\Lemuria\Exception\CommandParserException;
use Lemuria\Engine\Move;
use Lemuria\Engine\Turn;
use Lemuria\Exception\LemuriaException;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
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

	/**
	 * Initialize turn.
	 */
	public function __construct() {
		$this->priority = CommandPriority::getInstance();
		foreach (CommandPriority::ORDER as $priority) {
			$this->queue[$priority] = [];
		}
	}

	/**
	 * Add commands.
	 */
	public function add(Move $move): Turn {
		Lemuria::Log()->debug('Adding party move.', ['move' => $move]);
		$context = new Context($this);
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
		return $this;
	}

	/**
	 * @throws LemuriaException
	 */
	public function addEffect(Effect $effect): Turn {
		$this->enqueue($effect);
		return $this;
	}

	/**
	 * Evaluate the whole turn.
	 */
	public function evaluate(): Turn {
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
