<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class GuardBorderMessage extends GuardMessage
{
	protected string $direction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will block the ' . $this->direction . ' border.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->direction($name, useFullName: true) ?? parent::getTranslation($name);
	}
}
