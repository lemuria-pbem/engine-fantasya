<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Entity;
use Lemuria\Lemuria;

trait MessageTrait
{
	use BuilderTrait;

	protected function message(string $messageType, ?Entity $target = null): LemuriaMessage {
		$id      = Lemuria::Report()->nextId();
		$message = new LemuriaMessage();
		return $this->initMessage($message, $target)->setType(self::createMessageType($messageType))->setId($id);
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $target ? $message->setAssignee($target->Id()) : $message;
	}
}
