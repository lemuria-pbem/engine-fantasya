<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class RawMaterialOnlyMessage extends RawMaterialOutputMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only produce ' . $this->output . ' with ' . $this->talent . '.';
	}
}
