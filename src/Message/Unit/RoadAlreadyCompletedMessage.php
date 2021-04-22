<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class RoadAlreadyCompletedMessage extends RoadInOceanMessage
{
	protected string $direction;

	protected function create(): string {
		return 'The road to ' . $this->direction . ' in region ' . $this->region . ' is already completed.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'direction') {
			return $this->translateKey('world.' . $this->direction);
		}
		return parent::getTranslation($name);
	}
}
