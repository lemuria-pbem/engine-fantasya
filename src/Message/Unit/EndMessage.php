<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class EndMessage extends UnitMessage
{
	protected string $tempNumber;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'End of temp unit ' . $this->tempNumber . ', returning to unit ' . $this->id . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->tempNumber = $message->getParameter();
	}
}
