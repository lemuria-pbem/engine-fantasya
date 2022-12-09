<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class SiegeMessage extends SiegeNotFoundMessage
{
	protected Result $result = Result::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' sieges construction ' . $this->construction . '.';
	}
}
