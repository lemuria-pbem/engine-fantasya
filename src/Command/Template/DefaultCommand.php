<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Template;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\DefaultInvalidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DefaultMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DefaultUnknownMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;

/**
 * This command defines unit commands that are written to the order template.
 *
 * - VORLAGE <command>
 * - VORLAGE '<command>'
 * - VORLAGE "<command>"
 */
final class DefaultCommand extends AbstractTemplate
{
	protected function run(): void {
		$default = $this->cleanLine($this->phrase);
		$default = $this->replaceTempUnits($default, 1);
		try {
			$context = new Context(new State());
			$context->setParty($this->unit->Party())->setUnit($this->unit);
			$command = $context->Factory()->create(new Phrase($default))->getDelegate();
		} catch (CommandParserException|UnknownCommandException) {
			$this->message(DefaultUnknownMessage::class)->p($default);
			return;
		}
		if ($command instanceof UnitCommand) {
			$this->context->getProtocol($this->unit)->addDefault($command);
			$this->message(DefaultMessage::class)->p($default);
		} else {
			$this->message(DefaultInvalidMessage::class)->p($default);
		}
	}
}
