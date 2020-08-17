<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Model\Lemuria\Combat;

class FightMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $position;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' will ' . $this->getPosition() . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->position = $message->getParameter();
	}

	/**
	 * @return string
	 */
	private function getPosition(): string {
		switch ($this->position) {
			case Combat::AGGRESSIVE :
				return 'fight till death';
			case Combat::DEFENSIVE :
				return 'fight defensive from the back row';
			case Combat::REFUGEE :
				return 'flee from any fight';
			case Combat::BACK :
				return 'fight from the back row';
			case Combat::BYSTANDER :
				return 'stand aside and watch the battle';
			case Combat::FRONT :
			default :
				return 'fight in the front row';
		}
	}
}
