<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Command\Exception\TempUnitException;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Immediate;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TempMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Entity;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Unit;

/**
 * Implementation of command MACHEN Temp (create new Unit).
 *
 * The command creates a new Unit and sets it as the current Unit.
 *
 * - MACHEN Temp
 * - MACHEN Temp <id>
 */
final class Temp extends UnitCommand implements Immediate
{
	private Unit $createdUnit;

	private Unit $creator;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		if ($context->Parser()->isSkip()) {
			$context->Parser()->skip();
		}
	}

	public function skip(): Immediate {
		return $this;
	}

	/** @noinspection PhpPossiblePolymorphicInvocationInspection */
	protected function run(): void {
		if ($this->context->UnitMapper()->has($this->getTempNumber())) {
			$this->context->Parser()->skip();
			throw new TempUnitException('TEMP unit ' . $this->getTempNumber() . ' is mapped already.');
		}

		$party = $this->context->Party();

		$this->creator     = $this->context->Unit();
		$this->createdUnit = new Unit();
		$id                = $this->createId();
		$this->createdUnit->setId($id)->setName('Einheit ' . $id)->setDescription('');
		$this->createdUnit->setRace($party->Race());

		$party->People()->add($this->createdUnit);
		$this->creator->Region()->Residents()->add($this->createdUnit);

		$this->context->UnitMapper()->map($this);
		$this->context->setUnit($this->createdUnit);

		$this->message(TempMessage::class);
	}

	/**
	 * Get creator Unit.
	 *
	 * @throws CommandException
	 */
	public function getCreator(): Unit {
		if (!$this->creator) {
			throw new CommandException('Unit was not yet created.');
		}
		return $this->creator;
	}

	/**
	 * Get created Unit.
	 *
	 * @throws CommandException
	 */
	public function getUnit(): Unit {
		if (!$this->createdUnit) {
			throw new CommandException('Unit was not yet created.');
		}
		return $this->createdUnit;
	}

	/**
	 * Get TEMP number.
	 */
	#[Pure] public function getTempNumber(): string {
		return strtolower($this->phrase->getParameter(2));
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->setAssignee($this->createdUnit->Id());
	}

	/**
	 * Create new ID for created Unit.
	 */
	private function createId(): Id {
		$id     = null;
		$temp   = $this->getTempNumber();
		$number = (int)$temp;
		if ($number > 0) {
			$id = new Id($number);
		} else {
			try {
				$id = Id::fromId($temp);
			} catch (IdException) {
			}
		}

		if ($id && !Lemuria::Catalog()->has($id, Catalog::UNITS)) {
			return $id;
		}
		return Lemuria::Catalog()->nextId(Catalog::UNITS);
	}
}
