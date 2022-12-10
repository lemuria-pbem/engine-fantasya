<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;

class AttackNotFoundMessage extends AttackSelfMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected string $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find unit ' . $this->unit . ' to attack.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->getParameter();
	}
}
