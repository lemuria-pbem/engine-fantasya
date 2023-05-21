<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\UnitTrait;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Entity;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * Base class for all unit commands.
 */
abstract class UnitCommand extends AbstractCommand
{
	use UnitTrait;

	protected bool $preventDefault = false;

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->unit = $context->Unit();
	}

	public function Unit(): Unit {
		return $this->unit;
	}

	public function isPrepared(): bool {
		if ($this instanceof Activity && $this->IsDefault() && $this->unit->Size() <= 0) {
			return false;
		}
		return parent::isPrepared();
	}

	/**
	 * Execute the command.
	 *
	 * @throws CommandException
	 */
	public function execute(): Action {
		parent::execute();
		if ($this instanceof Activity) {
			$this->context->getProtocol($this->unit)->addNewDefaults($this);
			if ($this instanceof Reassignment) {
				Lemuria::Catalog()->addReassignment($this);
			}
		}
		return $this;
	}

	/**
	 * Get command as string.
	 */
	public function __toString(): string {
		return '[' . $this->unit->Id() . '] ' . parent::__toString();
	}

	/**
	 * Prevent that this command is used as new default.
	 */
	public function preventDefault(): Command {
		$this->preventDefault = true;
		return $this;
	}

	/**
	 * Make preparations before running the command.
	 */
	protected function initialize(): void {
		$this->context->setUnit($this->unit);
		if ($this->checkSize()) {
			$this->commitCommand($this);
		} else {
			Lemuria::Log()->debug('Command execution skipped due to empty unit.', ['command' => $this]);
		}
	}

	protected function checkSize(): bool {
		return $this->unit->Size() > 0;
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->setAssignee($target ? $target->Id() : $this->unit->Id());
	}

	protected function calculus(): Calculus {
		return $this->context->getCalculus($this->unit);
	}
}
