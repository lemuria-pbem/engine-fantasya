<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class VesselBuildMessage extends VesselOnlyMessage
{
	protected Result $result = Result::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' builds ' . $this->size . ' points in size on vessel ' . $this->vessel . '.';
	}
}
