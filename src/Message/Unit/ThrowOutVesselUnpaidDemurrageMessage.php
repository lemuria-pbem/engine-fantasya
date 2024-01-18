<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class ThrowOutVesselUnpaidDemurrageMessage extends ThrowOutFromVesselMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' cannot leave the vessel ' . $this->vessel . ' because demurrage has not been paid.';
	}
}
