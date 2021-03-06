<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\CommandParserException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\DefaultInvalidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DefaultMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DefaultUnknownMessage;
use Lemuria\Engine\Fantasya\Phrase;

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
