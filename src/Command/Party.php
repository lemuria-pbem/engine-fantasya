<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Immediate;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Message\Party\PartyInRegionsMessage;
use Lemuria\Engine\Lemuria\Message\Party\PartyMessage;
use Lemuria\Entity;
use Lemuria\Id;
use Lemuria\Model\Lemuria\Party as PartyModel;
use Lemuria\Model\Lemuria\Party\Census;
use Lemuria\Model\Lemuria\Region;

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

		$this->visitAllRegions($party);
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->setAssignee($this->context->Party()->Id());
	}

	private function visitAllRegions(PartyModel $party): void {
		$census = new Census($party);
		$atlas  = $census->getAtlas();
		foreach ($atlas as $region /* @var Region $region */) {
			$party->Chronicle()->add($region);
		}
		$this->message(PartyInRegionsMessage::class)->p($atlas->count());
	}
}
