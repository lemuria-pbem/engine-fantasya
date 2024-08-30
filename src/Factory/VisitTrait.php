<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect\VisitEffect;
use Lemuria\Engine\Fantasya\Effect\WelcomeVisitor;
use Lemuria\Engine\Fantasya\Factory\Model\Buzzes;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Unit;

trait VisitTrait
{
	private function addVisitEffect(Unit $unit): void {
		$score    = Lemuria::Score();
		$effect   = new VisitEffect(State::getInstance());
		$existing = $score->find($effect->setUnit($unit));
		if ($existing instanceof VisitEffect) {
			$effect = $existing;
		} else {
			$score->add($effect);
		}
		$effect->Parties()->add($this->unit->Party());
	}

	private function visitFrom(Unit $unit): ?Buzzes {
		if ($unit->Party()->Type() === Type::NPC) {
			$effect   = new WelcomeVisitor(State::getInstance());
			$existing = Lemuria::Score()->find($effect->setUnit($unit));
			if ($existing instanceof WelcomeVisitor) {
				Lemuria::Log()->debug('Visiting NPC ' . $unit . '...');
				$messages = $existing->Visitation()?->from($this->unit);
				if ($messages && !$messages->isEmpty()) {
					return $messages;
				}
			}
		}
		return null;
	}
}
