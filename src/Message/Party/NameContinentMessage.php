<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class NameContinentMessage extends NameContinentUndoMessage
{
	public const NAME = 'name';

	protected string $name;

	protected function create(): string {
		return 'Party ' . $this->id . ' has named the continent ' . $this->continent . ' to ' . $this->name . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter(self::NAME);
	}
}
