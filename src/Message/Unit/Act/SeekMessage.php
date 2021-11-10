<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

class SeekMessage extends RoamMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has found enemies in region ' . $this->region . '.';
	}
}
