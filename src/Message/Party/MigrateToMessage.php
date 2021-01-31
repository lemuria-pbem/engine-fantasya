<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class MigrateToMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $migrant;

	protected function create(): string {
		return 'Unit ' . $this->migrant . ' migrated to our party.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->migrant = $message->get();
	}
}
