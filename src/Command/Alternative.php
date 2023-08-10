<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Exception\InvalidAlternativeException;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * With ALTERNATIVE an activity command can be marked as alternative so that it does not throw an exception when
 * executed after another activity.
 *
 * ALTERNATIVE <command>
 */
final class Alternative extends DelegatedCommand
{
	protected function createDelegate(): Command {
		$n = $this->phrase->count();
		if ($n < 2) {
			throw new InvalidCommandException($this);
		}

		$phrase   = $this->phrase->getLine();
		$order    = $this->context->Factory()->create(new Phrase($phrase));
		$delegate = $order->getDelegate();
		if ($delegate instanceof Activity) {
			$delegate->setAlternative();
			return $order;
		}
		throw new InvalidAlternativeException($this->phrase);
	}
}
