<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class DutyMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Economy;

	protected Singleton $building;

	protected float $duty;

	protected function create(): string {
		return 'The duty for the ' . $this->building . ' ' . $this->id . ' has been set to ' . $this->duty . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->building = $message->getSingleton();
		$this->duty     = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'building') {
			$this->singleton($name, 'building');
		}
		return $this->percent($name, 'duty') ?? parent::getTranslation($name);
	}
}
