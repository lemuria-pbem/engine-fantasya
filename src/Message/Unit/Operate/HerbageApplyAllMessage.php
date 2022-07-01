<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message;

class HerbageApplyAllMessage extends HerbageApplyEmptyMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' adds all herb occurrences from almanac ' . $this->almanac . '.';
	}
}
