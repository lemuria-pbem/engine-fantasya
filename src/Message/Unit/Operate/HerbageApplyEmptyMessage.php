<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class HerbageApplyEmptyMessage extends HerbageWriteEmptyMessage
{
	protected Id $almanac;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot add herb occurrences from empty herb almanac ' . $this->almanac . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->almanac = $message->get();
	}
}
