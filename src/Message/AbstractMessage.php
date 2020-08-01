<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

use Lemuria\Engine\Message;
use Lemuria\Id;
use Lemuria\SingletonTrait;

abstract class AbstractMessage implements MessageType
{
	use SingletonTrait;

	protected string $level = Message::DEBUG;

	protected Id $id;

	/**
	 * @return string
	 */
	public function Level(): string {
		return $this->level;
	}

	/**
	 * @param LemuriaMessage $message
	 * @return string
	 */
	public function render(LemuriaMessage $message): string {
		$this->getData($message);
		return $this->create();
	}

	/**
	 * @param LemuriaMessage $message
	 */
	abstract protected function getData(LemuriaMessage $message): void;

	/**
	 * @return string
	 */
	abstract protected function create(): string;
}
