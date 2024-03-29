<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class SummonEntsMessage extends AbstractCastMessage
{
	protected Result $result = Result::Success;

	protected Id $ents;

	protected int $size;

	protected function create(): string {
		return 'Unit ' . $this->id . ' summons ' . $this->size . ' ents as unit ' . $this->ents . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->size = $message->getParameter();
	}
}
