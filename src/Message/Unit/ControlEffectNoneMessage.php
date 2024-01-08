<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class ControlEffectNoneMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough aura to maintain control over unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
