<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GiveNotFoundMessage extends GiveFailedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot give anything, target unit ' . $this->recipient . ' is not here.';
	}
}
