<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class ForgetLevelMessage extends ForgetMessage
{
	protected int $ability;

	protected function create(): string {
		return 'Unit ' . $this->id . ' forgets some knowledge in ' . $this->talent . ' down to level ' . $this->ability . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->ability = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'ability') ?? parent::getTranslation($name);
	}
}
