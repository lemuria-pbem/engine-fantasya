<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message\Result;

class ColorOutOfSpaceSummonMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected string $direction;

	protected function create(): string {
		return 'In the darkest hour in the nights vague low voices are in the air, and each time a gust of wind ' .
			   'comes up from ' . $this->direction . ' that soon gets calm again. Along the way some isolated sheet ' .
			   'lightning can be seen behind the clouds.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->direction($name, useFullName: true) ?? parent::getTranslation($name);
	}
}
