<?php
/** @noinspection DuplicatedCode */
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class SpellbookWriteMessage extends ScrollWriteNothingMessage
{
	public final const SPELL = 'spell';

	protected Result $result = Result::Success;

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
