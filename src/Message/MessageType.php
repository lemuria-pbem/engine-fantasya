<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

interface MessageType
{
	/**
	 * @return int
	 */
	public function Report(): int;

	/**
	 * @param LemuriaMessage $message
	 * @return string
	 */
	public function render(LemuriaMessage $message): string;
}
