<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Command\Exception\UnitException;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Immediate;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Message\Unit\UnitMessage;
use Lemuria\Engine\Lemuria\Phrase;
use Lemuria\Entity;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Lemuria\Unit as UnitModel;

/**
 * Implementation of command EINHEIT (start command section of a unit).
 *
 * The command sets the current executing Unit.
 */
final class Unit extends AbstractCommand implements Immediate
{
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		if ($context->Parser()->isSkip()) {
			$context->Parser()->skip(false);
		}
	}

	public function skip(): Immediate {
		return $this;
	}

	/**
	 * @throws UnitException
	 */
	protected function run(): void {
		$id = Id::fromId($this->phrase->getParameter());
		try {
			$unit = UnitModel::get($id);
		} catch (NotRegisteredException $e) {
			$this->context->Parser()->skip();
			throw $e;
		}
		if ($unit->Party() !== $this->context->Party()) {
			$this->context->Parser()->skip();
			throw new UnitException($unit, $this->context->Party());
		}
		$this->context->setUnit($unit);
		$this->message(UnitMessage::class);
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->e($this->context->Unit());
	}
}
