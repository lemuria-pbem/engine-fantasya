<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class CommentMessage extends AbstractUnitMessage
{
	protected string $comment;

	protected function create(): string {
		return 'Comment for unit ' . $this->id . ': ' . $this->comment;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->comment = $message->getParameter();
	}
}
