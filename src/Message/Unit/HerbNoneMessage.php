<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class HerbNoneMessage extends HerbUnknownMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has not found any herbs in region ' . $this->region . '.';
	}
}
