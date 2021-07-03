<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class LearnMagicMessage extends AbstractUnitMessage
{
	protected int $aura;

	protected function create(): string {
		return 'Unit ' . $this->id . ' levels up in Magic and gains ' . $this->aura . ' permanent aura.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->aura = $message->getParameter();
	}
}
