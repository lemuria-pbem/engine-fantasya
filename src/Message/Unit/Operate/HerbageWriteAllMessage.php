<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

class HerbageWriteAllMessage extends HerbageApplyEmptyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' writes all herb occurrences to the almanac ' . $this->almanac . '.';
	}
}
