<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

class FounderMessage extends DriftDamageMessage
{
	protected function create(): string {
		return 'Vessel ' . $this->id . ' sinks with everyone on board in region ' . $this->region . '.';
	}
}
