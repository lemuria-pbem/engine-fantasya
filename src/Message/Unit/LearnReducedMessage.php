<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class LearnReducedMessage extends LearnProgressMessage
{
	public final const string SHIP = 'ship';

	protected Result $result = Result::Failure;

	protected Singleton $ship;

	protected function create(): string {
		return 'Unit ' . $this->id . ' aboard can learn ' . $this->talent . ' with ' . $this->experience . ' experience only.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->ship = $message->getSingleton(self::SHIP);
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, self::SHIP) ?? parent::getTranslation($name);
	}
}
