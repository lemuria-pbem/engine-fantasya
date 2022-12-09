<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class MigrateFromMessage extends MigrateToMessage
{
	public final const PARTY = 'party';

	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->migrant . ' migrated to party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get(self::PARTY);
	}
}
