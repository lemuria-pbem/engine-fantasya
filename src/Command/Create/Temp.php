<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Command\Exception\TempUnitExistsException;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Immediate;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TempMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Entity;
use Lemuria\Exception\IdException;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Navigable;
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
	private ?Unit $createdUnit = null;

	private ?Unit $creator = null;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		if ($context->Parser()->isSkip()) {
			$context->Parser()->skip();
		}
	}

	public function skip(): static {
		return $this;
	}

	protected function run(): void {
		if ($this->context->UnitMapper()->has($this->getTempNumber())) {
			$this->context->Parser()->skip();
			throw new TempUnitExistsException($this->getTempNumber());
		}

		$party = $this->context->Party();

		$this->creator     = $this->context->Unit();
		$this->createdUnit = new Unit();
		$id                = $this->createId();
		$this->createdUnit->setName('Einheit ' . $id)->setId($id);
		$this->createdUnit->setRace($party->Race());

		$presettings = $party->Presettings();
		$this->createdUnit->setBattleRow($presettings->BattleRow());
		$this->createdUnit->setIsLooting($presettings->IsLooting());
		$this->createdUnit->setIsHiding($presettings->IsHiding());
		$this->createdUnit->setDisguise($presettings->Disguise());

		$party->People()->add($this->createdUnit);
		$region = $this->creator->Region();
		$region->Residents()->add($this->createdUnit);
		if ($region->Landscape() instanceof Navigable) {
			$vessel = $this->creator->Vessel();
			if ($vessel) {
				$vessel->Passengers()->add($this->createdUnit);
			}
		}

		$this->context->UnitMapper()->map($this);
		$this->context->setUnit($this->createdUnit);

		$this->message(TempMessage::class);
	}

	/**
	 * Get creator Unit.
	 */
	public function getCreator(): Unit {
		if (!$this->creator) {
			throw new LemuriaException('Unit was not yet created.');
		}
		return $this->creator;
	}

	/**
	 * Get created Unit.
	 */
	public function getUnit(): Unit {
		if (!$this->createdUnit) {
			throw new LemuriaException('Unit was not yet created.');
		}
		return $this->createdUnit;
	}

	/**
	 * Get TEMP number.
	 */
	public function getTempNumber(): string {
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

		if ($id && !Lemuria::Catalog()->has($id, Domain::Unit)) {
			return $id;
		}
		return Lemuria::Catalog()->nextId(Domain::Unit);
	}
}
