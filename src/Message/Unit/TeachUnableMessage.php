<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class TeachUnableMessage extends AbstractUnitMessage
{
	public const STUDENT = 'student';

	protected string $level = Message::FAILURE;

	private Id $student;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->student . ' anymore.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->student = $message->get(self::STUDENT);
	}
}
