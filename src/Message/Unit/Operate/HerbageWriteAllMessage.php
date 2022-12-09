<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message\Result;

class HerbageWriteAllMessage extends HerbageApplyEmptyMessage
{
	protected Result $result = Result::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' writes all herb occurrences to the almanac ' . $this->almanac . '.';
	}
}
