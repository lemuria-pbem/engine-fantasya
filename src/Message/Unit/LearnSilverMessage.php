<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class LearnSilverMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Singleton $talent;

	protected int $silver;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays ' . $this->silver . ' silver to learn ' . $this->talent . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton();
		$this->silver = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->talent($name, 'talent') ?? parent::getTranslation($name);
	}
}
