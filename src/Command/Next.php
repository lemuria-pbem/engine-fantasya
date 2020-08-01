<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Immediate;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Message\Party\NextMessage;
use Lemuria\Engine\Lemuria\Phrase;

/**
 * Implementation of command NÄCHSTER (this should be the final command in a party's turn).
 *
 * The command requests end of command execution.
 */
final class Next extends AbstractCommand implements Immediate
{
	/**
	 * Create a new command for given Phrase.
	 *
	 * @param Phrase $phrase
	 * @param Context $context
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		while ($context->Parser()->isSkip()) {
			$context->Parser()->skip(false);
		}
	}

	/**
	 * Skip the command.
	 *
	 * @return Immediate
	 */
	public function skip(): Immediate {
		return $this;
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$this->context->Parser()->finish();
		$this->message(NextMessage::class);
	}

	/**
	 * @param LemuriaMessage $message
	 * @return LemuriaMessage
	 */
	protected function initMessage(LemuriaMessage $message): LemuriaMessage {
		return $message->e($this->context->Party());
	}
}
