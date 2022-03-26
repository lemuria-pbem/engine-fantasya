<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class InciteMonsterNoMessage extends InciteMonsterNoEnemiesMessage
{
	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot target the unit ' . $this->unit . ' in the Incite Monster spell.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
