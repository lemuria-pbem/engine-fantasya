<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Singleton;

class CarcassNotMessage extends CarcassNothingMessage
{
	public final const ITEM = 'item';

	protected Reliability $reliability = Reliability::Determined;

	protected Singleton $item;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find any ' . $this->item . ' in ' . $this->composition . ' ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->item = $message->getSingleton(self::ITEM);
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'item') ?? parent::getTranslation($name);
	}
}
