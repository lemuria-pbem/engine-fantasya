<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Message\CommentMessage;

/**
 * The comment command repeats the comment line in the party's report.
 * - //
 * - KOMMENTAR
 */
final class Comment extends UnitCommand {

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$this->message(CommentMessage::class)->p($this->phrase->getLine());
	}
}
