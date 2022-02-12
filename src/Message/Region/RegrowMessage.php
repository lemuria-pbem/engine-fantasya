<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class RegrowMessage extends AbstractRegionMessage
{
	protected Section $section = Section::ECONOMY;

	protected Singleton $herb;

	protected float $occurrence;

	protected function create(): string {
		return 'Herb occurrence of ' . $this->herb . ' is ' . $this->occurrence . ' in region ' . $this->id . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->herb       = $message->getSingleton();
		$this->occurrence = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'herb') {
			return $this->commodity($name, 'herb');
		}
		if ($name === 'occurrence') {
			return $this->number($name, 'occurrence');
		}
		return parent::getTranslation($name);
	}
}
