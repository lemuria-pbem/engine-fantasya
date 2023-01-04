<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command\Exception\UnitException;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Immediate;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnitMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Entity;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Unit as UnitModel;

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
		$id = $this->parseId();
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
		$this->context->getProtocol($unit);
		$this->message(UnitMessage::class);
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->setAssignee($this->context->Unit()->Id());
	}
}
