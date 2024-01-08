<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class ControlEffectMessage extends ControlEffectNoneMessage
{
	protected int $aura;

	protected function create(): string {
		return 'Unit ' . $this->id . ' consumes ' . $this->aura . ' aura to maintain control over unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->aura = $message->getParameter();
	}
}
