<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\ForgetLevelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ForgetLowerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ForgetMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ForgetUnknownMessage;
use Lemuria\Model\Fantasya\Ability;

/**
 * Forget a talent.
 *
 * - VERGESSEN <talent>
 * - VERGESSEN <talent> <level>
 */
final class Forget extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1 || $n > 2) {
			throw new InvalidCommandException($this);
		}

		$talent    = $this->context->Factory()->talent($this->phrase->getParameter());
		$knowledge = $this->unit->Knowledge();
		if ($knowledge->offsetExists($talent)) {
			$level = (int)$this->phrase->getParameter(2);
			if ($level <= 0) {
				$knowledge->remove($knowledge->offsetGet($talent));
				$this->message(ForgetMessage::class)->s($talent);
			} else {
				$current = $knowledge->offsetGet($talent);
				if ($current->Level() > $level) {
					$experience = $current->Experience();
					$forget     = $experience - Ability::getExperience($level);
					$knowledge->remove(new Ability($talent, $forget));
					$this->message(ForgetLevelMessage::class)->s($talent)->p($level);
				} else {
					$this->message(ForgetLowerMessage::class)->s($talent)->p($current->Level());
				}
			}
		} else {
			$this->message(ForgetUnknownMessage::class)->s($talent);
		}
	}
}
