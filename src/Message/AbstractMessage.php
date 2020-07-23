<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

abstract class AbstractMessage implements MessageType
{
	/**
	 * @param LemuriaMessage $message
	 * @return string
	 */
	public function render(LemuriaMessage $message): string {
		$this->getEntities($message);
		return $this->create();
	}

	/**
	 * @param LemuriaMessage $message
	 */
	abstract protected function getEntities(LemuriaMessage $message): void;

	/**
	 * @return string
	 */
	abstract protected function create(): string;
}
