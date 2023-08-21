<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Template;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Message\Unit\DefaultMessage;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * This command defines unit commands that are written to the order template.
 *
 * VORLAGE <n> <command>
 * VORLAGE * <command>
 * VORLAGE *<n> <command>
 * VORLAGE <n>/<m> <command>
 * @ <n> <command>
 * @ * <command>
 * @ *<n> <command>
 * @ <n>/<m> <command>
 */
final class Copy extends AbstractTemplate
{
	private ?UnitCommand $current = null;

	public function setCurrent(UnitCommand $current): Copy {
		$this->current = $current;
		return $this;
	}

	protected function run(): void {
		$this->phrase = $this->cleanPhrase();
		$this->context->getProtocol($this->unit)->addDefault($this);
		$this->message(DefaultMessage::class)->p((string)$this->phrase);
	}

	protected function commitCommand(UnitCommand $command): void {
		$protocol = $this->context->getProtocol($this->unit);
		$protocol->logCurrent($this->current ?? $this);
	}

	private function cleanPhrase(): Phrase {
		$default  = $this->cleanLine($this->phrase, 3);
		$default  = $this->replaceTempUnits($default);
		$copy     = $this->phrase->getVerb();
		$repeat   = $this->phrase->getParameter();
		$command  = $this->phrase->getParameter(2);
		return new Phrase($copy . ' ' . $repeat . ' ' . $command . ' ' . $default);
	}
}
