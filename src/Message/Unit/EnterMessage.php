<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class EnterMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Id $construction;

	protected function create(): string {
		return 'Unit '. $this->id . ' enters the construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get();
	}
}
