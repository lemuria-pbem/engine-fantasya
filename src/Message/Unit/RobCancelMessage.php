<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class RobCancelMessage extends RobSelfMessage
{
	protected Id $unit;

	protected function create(): string {
		return 'The robbery against ' . $this->unit . ' is cancelled - we must defend ourselves.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
