<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Use\Apply;
use Lemuria\Engine\Fantasya\Command\Use\Excert;
use Lemuria\Engine\Fantasya\Command\Use\Operate;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\OperateTrait;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Composition\Scroll;
use Lemuria\Model\Fantasya\Composition\Spellbook;

/**
 * Implementation of command BENUTZEN.
 *
 * The command determines the sub command and delegates to it.
 *
 * Apply:
 * - BENUTZEN <potion>
 * - BENUTZEN <amount> <potion>
 * Operate:
 * - BENUTZEN <Unicum>
 * - BENUTZEN <composition> <Unicum>
 */
final class UseCommand extends DelegatedCommand
{
	private const ACTIVITY_COMPOSITE = [Scroll::class => true, Spellbook::class => true];

	use OperateTrait;

	/**
	 * Create the delegate.
	 */
	protected function createDelegate(): Command {
		$n = count($this->phrase);
		if ($n < 1) {
			throw new InvalidCommandException($this);
		}

		$param = $this->phrase->getParameter();
		if ($n === 1) {
			try {
				$id = Id::fromId($param);
				if ($this->unit->Treasury()->has($id)) {
					return $this->createOperateCommand();
				}
			} catch (IdException) {
			}
			return new Apply($this->phrase, $this->context);
		}

		$amount = (int)$param;
		if ((string)$amount === $param) {
			return new Apply($this->phrase, $this->context);
		}
		return $this->createOperateCommand();
	}

	private function createOperateCommand(): UnitCommand {
		$composition = $this->parseComposition()::class;
		$isActivity  = isset(self::ACTIVITY_COMPOSITE[$composition]);
		return $isActivity ? new Operate($this->phrase, $this->context) : new Excert($this->phrase, $this->context);
	}
}
