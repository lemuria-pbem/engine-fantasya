<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class LearnProgressMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	private Singleton $talent;

	private int $experience;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' learns ' . $this->talent . ' with ' . $this->experience . ' experience.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton();
		$this->experience = $message->getParameter();
	}
}
