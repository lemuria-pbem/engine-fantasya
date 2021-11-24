<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Template;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Message\Unit\DefaultMessage;

/**
 * This command defines unit commands that are written to the order template.
 *
 * VORLAGE <n> <command>
 * VORLAGE * <command>
 * VORLAGE <n>/<m> <command>
 * @ <n> <command>
 * @ * <command>
 * @ <n>/<m> <command>
 */
final class Copy extends UnitCommand
{
	protected function run(): void {
		$this->context->getProtocol($this->unit)->addDefault($this);
		$this->message(DefaultMessage::class)->p((string)$this->phrase);
	}
}
