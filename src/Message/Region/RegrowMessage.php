<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class RegrowMessage extends AbstractRegionMessage
{
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
		return $this->commodity($name, 'herb') ?? parent::getTranslation($name);
	}
}
