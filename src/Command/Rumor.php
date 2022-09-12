<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Effect\Rumors;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\RumorMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * Add a rumor for visiting units.
 *
 * - GERÜCHT <text>
 */
final class Rumor extends UnitCommand
{
	protected function run(): void {
		$effect   = new Rumors(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($this->unit));
		if ($existing instanceof Rumors) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
		}

		$rumor = Describe::trimDescription($this->phrase->getParameter());
		if (!$rumor) {
			throw new InvalidCommandException($this);
		}
		$rumors = $effect->Rumors();
		$rumors[$rumors->count()] = $rumor;
		$this->message(RumorMessage::class)->p($rumor);
	}
}
