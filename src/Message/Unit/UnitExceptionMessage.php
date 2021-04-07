<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class UnitExceptionMessage extends AbstractUnitMessage
{
	public const ACTION = 'action';

	protected string $level = Message::ERROR;

	protected string $exception;

	protected string $action;

	protected function create(): string {
		return $this->action . ' - ' . $this->exception;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->exception = $message->getParameter();
		$this->action    = $message->getParameter(self::ACTION);
	}

	protected function getTranslation(string $name): string {
		if ($name === 'exception') {
			if (str_starts_with($this->exception, 'Unknown command')) {
				return str_replace('Unknown command', 'unbekannter Befehl', $this->exception);
			}
			if (str_starts_with($this->exception, 'Unknown item')) {
				return str_replace('Unknown item', 'unbekannte Ressource', $this->exception);
			}
		}
		return parent::getTranslation($name);
	}
}