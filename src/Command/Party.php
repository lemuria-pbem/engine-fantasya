<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Immediate;
use Lemuria\Engine\Lemuria\Message\Unit\PartyMessage;
use Lemuria\Id;
use Lemuria\Model\Lemuria\Party as PartyModel;

/**
 * Implementation of command PARTEI (this should be the first command in a party's turn).
 *
 * The command sets the current Party.
 */
final class Party extends AbstractCommand implements Immediate
{
	/**
	 * Skip the command.
	 *
	 * @return Immediate
	 */
	public function skip(): Immediate {
		return $this;
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$id    = Id::fromId($this->phrase->getParameter());
		$party = PartyModel::get($id);
		$this->context->setParty($party);
		$this->message(PartyMessage::class)->e($party);
	}
}
