<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Immediate;
use Lemuria\Engine\Fantasya\Message\Party\NotImplementedMessage;
use Lemuria\Lemuria;

final class NotImplementedCommand extends AbstractCommand implements Immediate
{
	public function skip(): Immediate {
		return $this;
	}

	protected function run(): void {
		Lemuria::Log()->debug('Command ' . $this . ' is not implemented.');
		$this->message(NotImplementedMessage::class, $this->context->Party())->p($this->phrase->getVerb());
	}
}
