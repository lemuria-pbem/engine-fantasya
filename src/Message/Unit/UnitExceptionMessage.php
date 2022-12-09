<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class UnitExceptionMessage extends AbstractUnitMessage
{
	public final const ACTION = 'action';

	protected Result $result = Result::Error;

	protected Section $section = Section::Error;

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
			if (preg_match('/^Unit ([0-9a-z]+) cannot have more than one activity.$/', $this->exception, $matches) === 1) {
				return 'Die Einheit ' . $matches[1] . ' hat bereits einen langen Befehl ausgeführt';
			}
		}
		return parent::getTranslation($name);
	}
}
