<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class TeachForeignMessage extends TeachStudentMessage
{
	protected Singleton $talent;

	protected int $ability;

	protected function create(): string {
		return 'Student ' . $this->student . ' now has level ' . $this->ability . ' in ' . $this->talent . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent  = $message->getSingleton();
		$this->ability = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'talent') {
			return $this->talent($name, 'talent');
		}
		return $this->number($name, 'ability') ?? parent::getTranslation($name);
	}
}
