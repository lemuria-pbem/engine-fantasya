<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

use Lemuria\Singleton;

interface MessageType extends Singleton
{
	/**
	 * @return string
	 */
	public function Level(): string;

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
