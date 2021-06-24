<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class SortAfterMessage extends AbstractUnitMessage
{
	protected Id $other;

	protected function create(): string {
		return 'Unit ' . $this->id . ' reordered after unit ' . $this->other . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->other = $message->get();
	}
}
