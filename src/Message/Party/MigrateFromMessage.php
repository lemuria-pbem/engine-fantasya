<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;

class MigrateFromMessage extends MigrateToMessage
{
	public const PARTY = 'party';

	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->migrant . ' migrated to party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get(self::PARTY);
	}
}
