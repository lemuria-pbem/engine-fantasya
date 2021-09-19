<?php
/** @noinspection GrazieInspection */
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Immediate;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\PartyMessage;
use Lemuria\Entity;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Party as PartyModel;

/**
 * Implementation of command PARTEI (this should be the first command in a party's turn).
 *
 * The command sets the current Party.
 */
final class Party extends AbstractCommand implements Immediate
{
	public function skip(): Immediate {
		return $this;
	}

	protected function run(): void {
		$id    = Id::fromId($this->phrase->getParameter());
		$party = PartyModel::get($id);
		$this->context->setParty($party);
		$this->message(PartyMessage::class);
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->setAssignee($this->context->Party()->Id());
	}
}
