<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use function Lemuria\number;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;
use Lemuria\Item;

class RaiseTheDeadMessage extends AbstractCastMessage
{
	protected Result $result = Result::Success;

	protected Id $unit;

	protected Item $gang;

	protected function create(): string {
		return 'Unit ' . $this->id . ' raises ' . $this->gang . ' from the dead as unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
		$this->gang = $message->getGang();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'gang') {
			return number($this->gang->Count()) . ' ' . $this->translateItem($this->gang, Casus::Accusative);
		}
		return parent::getTranslation($name);
	}
}
