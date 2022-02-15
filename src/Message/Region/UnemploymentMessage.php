<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;

class UnemploymentMessage extends AbstractRegionMessage
{
	protected Section $section = Section::ECONOMY;

	protected int $recruits;

	protected function create(): string {
		return $this->recruits . ' recruits are available in region ' . $this->id . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->recruits = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'recruits') ?? parent::getTranslation($name);
	}
}
