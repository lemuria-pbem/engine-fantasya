<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Message\Unit\CommentMessage;

/**
 * The comment command repeats the comment line in the party's report.
 * - //
 * - KOMMENTAR
 */
final class Comment extends UnitCommand
{
	protected function run(): void {
		$this->message(CommentMessage::class)->p($this->phrase->getLine());
	}
}
