<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BestowRejectedMessage extends GiveNotFoundMessage
{
	protected function create(): string {
		return 'Unit ' . $this->recipient . ' wanted to give ' . $this->id . ' an unicum.';
	}
}
