<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message;
use Lemuria\Id;

class DriftDamageMessage extends AbstractVesselMessage
{
	protected string $level = Message::EVENT;

	protected Id $region;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is damaged as it runs aground the reef off the coast of region ' . $this->region . ' because captain and crew cannot steer it anymore.';
	}
}
