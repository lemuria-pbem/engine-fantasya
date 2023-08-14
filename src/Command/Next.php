<?php
/** @noinspection GrazieInspection */
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Immediate;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\NextMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Entity;

/**
 * Implementation of command NÄCHSTER (this should be the final command in a party's turn).
 *
 * The command requests end of command execution.
 */
final class Next extends AbstractCommand implements Immediate
{
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		while ($context->Parser()->isSkip()) {
			$context->Parser()->skip(false);
		}
	}

	public function skip(): static {
		return $this;
	}

	protected function run(): void {
		$this->context->Parser()->finish();
		$this->message(NextMessage::class);
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->setAssignee($this->context->Party()->Id());
	}
}
