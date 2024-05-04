<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Destroy;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\ControlEffect;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Immediate;
use Lemuria\Engine\Fantasya\Message\Unit\DeliverMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DeliverUncontrolledMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

/**
 * This command delivers a controlled unit (generally a summoned monster bound by a spell).
 *
 * - ENTLASSEN <monster id>
 */
final class Deliver extends UnitCommand implements Immediate
{
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		if ($context->Parser()->isSkip()) {
			$context->Parser()->skip();
		}
	}

	public function skip(): static {
		return $this;
	}

	public function inject(): static {
		$this->run();
		return $this;
	}

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n !== 1) {
			throw new InvalidCommandException($this);
		}

		$unit     = $this->nextId($n);
		$effect   = new ControlEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($unit));
		if ($existing instanceof ControlEffect) {
			if ($existing->Summoner()->Party() === $this->context->Party()) {
				$existing->deliver();
				$this->message(DeliverMessage::class)->e($unit);
				return;
			}
		}
		$this->message(DeliverUncontrolledMessage::class)->e($unit);
	}
}
