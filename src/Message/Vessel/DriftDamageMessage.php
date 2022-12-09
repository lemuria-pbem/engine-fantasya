<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class DriftDamageMessage extends AbstractVesselMessage
{
	protected Result $result = Result::EVENT;

	protected Id $region;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is damaged as it runs aground the reef off the coast of region ' . $this->region . ' because captain and crew cannot steer it anymore.';
	}
}
