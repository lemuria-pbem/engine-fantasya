<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Singleton;

class ReadMessage extends AbstractPartyMessage
{
	public final const UNICUM = 'unicum';

	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::MAGIC;

	protected Id $unit;

	protected Singleton $composition;

	protected Id $unicum;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' examines ' . $this->composition . ' ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit        = $message->get();
		$this->composition = $message->getSingleton();
		$this->unicum      = $message->get(self::UNICUM);
	}

	protected function getTranslation(string $name): string {
		return $this->composition($name, 'composition') ?? parent::getTranslation($name);
	}
}
