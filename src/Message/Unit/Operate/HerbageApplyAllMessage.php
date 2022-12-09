<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message\Result;

class HerbageApplyAllMessage extends HerbageApplyEmptyMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->id . ' adds all herb occurrences from almanac ' . $this->almanac . '.';
	}
}
