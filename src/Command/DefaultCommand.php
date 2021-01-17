<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\CommandParserException;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Message\Unit\DefaultAlreadyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DefaultInvalidMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DefaultMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DefaultUnknownMessage;
use Lemuria\Engine\Lemuria\Phrase;

/**
 * This command defines unit commands that are written to the order template.
 *
 * - VORLAGE <command>
 * - VORLAGE '<command>'
 * - VORLAGE "<command>"
 */
final class DefaultCommand extends UnitCommand
{
	protected function run(): void {
		$default = trim($this->phrase->getLine(), "'\"");
		try {
			$command = $this->context->Factory()->create(new Phrase($default))->getDelegate();
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
