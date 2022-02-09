<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class ScrollWriteMessage extends ScrollWriteNothingMessage
{
	public final const SPELL = 'spell';

	protected string $level = Message::SUCCESS;

	protected Singleton $spell;

	protected function create(): string {
		return 'Unit ' . $this->id . ' writes ' . $this->spell . ' on the ' . $this->composition . ' ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->spell = $message->getSingleton(self::SPELL);
	}

	protected function getTranslation(string $name): string {
		return $this->spell($name, self::SPELL) ?? parent::getTranslation($name);
	}
}
