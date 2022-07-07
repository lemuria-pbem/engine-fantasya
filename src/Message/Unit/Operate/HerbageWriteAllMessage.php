<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message;

class HerbageWriteAllMessage extends HerbageApplyEmptyMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' writes all herb occurrences to the almanac ' . $this->almanac . '.';
	}
}
