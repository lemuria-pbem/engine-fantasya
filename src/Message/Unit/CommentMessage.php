<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class CommentMessage extends UnitMessage
{
	private string $comment;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Comment for unit ' . $this->id . ': ' . $this->comment;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->comment = $message->getParameter();
	}
}
