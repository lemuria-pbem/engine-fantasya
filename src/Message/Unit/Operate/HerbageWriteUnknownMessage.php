<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

class HerbageWriteUnknownMessage extends HerbageApplyUnknownMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot write herb occurrence of unknown region ' . $this->region . '.';
	}
}
