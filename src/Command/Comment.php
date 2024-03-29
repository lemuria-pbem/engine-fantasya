<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\CommentMessage;

/**
 * The comment command repeats the comment line in the party's report.
 * - //
 * - KOMMENTAR
 */
final class Comment extends UnitCommand
{
	public function Line(): string {
		return $this->phrase->getLine();
	}

	protected function run(): void {
		$this->context->getProtocol($this->unit)->addDefault($this);
		$this->message(CommentMessage::class)->p($this->Line());
	}
}
