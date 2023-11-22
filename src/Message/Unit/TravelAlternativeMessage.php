<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class TravelAlternativeMessage extends TravelExploreLandMessage
{
	public final const string ALTERNATIVE = 'alternative';

	protected string $direction;

	protected string $alternative;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has chosen alternative direction ' . $this->alternative . ' because there is water in ' . $this->direction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction   = $message->getParameter();
		$this->alternative = $message->getParameter(self::ALTERNATIVE);
	}

	protected function getTranslation(string $name): string {
		if ($name === self::ALTERNATIVE) {
			return $this->direction($name, self::ALTERNATIVE);
		}
		return $this->direction($name) ?? parent::getTranslation($name);
	}
}
