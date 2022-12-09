<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;
use Lemuria\Singleton;

class BestowMessage extends AbstractUnitMessage
{
	public final const UNICUM = 'unicum';

	protected Result $result = Result::Success;

	protected Id $unit;

	protected Id $unicum;

	protected Singleton $composition;

	protected function create(): string {
		return 'Unit ' . $this->id . ' bestows ' . $this->composition . ' ' . $this->unicum . ' to unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit        = $message->get();
		$this->unicum      = $message->get(self::UNICUM);
		$this->composition = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->composition($name, 'composition') ?? parent::getTranslation($name);
	}
}
