<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

class HerbageApplyUnknownMessage extends HerbageWriteEmptyMessage
{
	protected string $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot add herb occurrence of unknown region ' . $this->region . '.';
	}
}
