<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Immediate;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Message\Party\NextMessage;
use Lemuria\Engine\Lemuria\Phrase;
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

	public function skip(): Immediate {
		return $this;
	}

	protected function run(): void {
		$this->context->Parser()->finish();
		$this->message(NextMessage::class);
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->e($this->context->Party());
	}
}
