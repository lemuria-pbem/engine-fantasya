<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Lemuria\Exception\MessageEntityException;
use Lemuria\Engine\Lemuria\Message\AbstractMessage;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Report;
use Lemuria\Model\Lemuria\Party;

abstract class AbstractPartyMessage extends AbstractMessage
{
	private ?Party $party = null;

	/**
	 * @return int
	 */
	public function Report(): int {
		return Report::PARTY;
	}

	/**
	 * @return Party
	 */
	protected function Party(): Party {
		if (!$this->party) {
			throw new MessageEntityException(Party::class);
		}
		return $this->party;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getEntities(LemuriaMessage $message): void {
		$this->party = Party::get($message->get(Party::class));
	}
}
