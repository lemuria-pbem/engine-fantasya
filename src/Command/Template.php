<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Template\Copy;
use Lemuria\Engine\Fantasya\Command\Template\DefaultCommand;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * The VORLAGE command has two use cases that this composite command delegates to: simple default commands that are
 * finally executed, and repeated commands that are executed and set as default for a future round.
 *
 * VORLAGE <command>
 * VORLAGE <n> <command>
 * VORLAGE * <command>
 * VORLAGE <n>/<m> <command>
 * @ <command>
 */
final class Template extends DelegatedCommand
{
	protected function createDelegate(): Command {
		$n = $this->phrase->count();
		if ($n >= 2) {
			$verb   = $this->phrase->getVerb();
			$param  = $this->phrase->getParameter();
			$phrase = $this->phrase->getLine(2);

			if (preg_match('/^(0*\*|\*0*)$/', $param) === 1) {
				$copy    = $verb . ' * ' . $phrase;
				$command = new CompositeCommand($this->phrase, $this->context);
				return $command->setCommands([
					new Copy(new Phrase($copy), $this->context),
					$this->createOrder($phrase)
				]);
			}

			if (preg_match('/^([0-9]+)\*$/', $param, $matches) === 1 || preg_match('/^\*([0-9]+)$/', $param, $matches) === 1) {
				$limit = (int)$matches[1];
				if ($limit >= 2) {
					$copy    = $verb . ' *' . --$limit . ' ' . $phrase;
					$command = new CompositeCommand($this->phrase, $this->context);
					return $command->setCommands([
						new Copy(new Phrase($copy), $this->context),
						$this->createOrder($phrase)
					]);
				}
				if ($limit > 0) {
					return $this->createOrder($phrase);
				}
				$copy    = $verb . ' * ' . $phrase;
				$command = new CompositeCommand($this->phrase, $this->context);
				return $command->setCommands([
					new Copy(new Phrase($copy), $this->context),
					$this->createOrder($phrase)
				]);
			}

			$round = (int)$param;
			if ($round > 0 && (string)$round === $param) {
				if ($round > 1) {
					$command = $verb . ' ' . ($round > 2 ? --$round : '') . ' ' . $phrase;
				} else {
					$command = $phrase;
				}
				return new Copy(new Phrase($command), $this->context);
			}

			if (preg_match('#^([0-9]+)/([0-9]+)$#', $param, $matches) === 1) {
				$round    = (int)$matches[1];
				$interval = (int)$matches[2];
				if ($round > 0) {
					$command = $verb . ' ' . --$round . '/' . $interval . ' ' . $phrase;
					return new Copy(new Phrase($command), $this->context);
				}
				$copy    = $verb . ' ' . ($interval - 1) . '/' . $interval . ' ' . $phrase;
				$command = new CompositeCommand($this->phrase, $this->context);
				return $command->setCommands([
					new Copy(new Phrase($copy), $this->context),
					$this->createOrder($phrase)
				]);
			}
		}

		return new DefaultCommand($this->phrase, $this->context);
	}

	private function createOrder(string $phrase): Command {
		$order    = $this->context->Factory()->create(new Phrase($phrase));
		$delegate = $order->getDelegate();
		if ($delegate instanceof Activity) {
			$delegate->preventDefault();
		}
		return $order;
	}
}
