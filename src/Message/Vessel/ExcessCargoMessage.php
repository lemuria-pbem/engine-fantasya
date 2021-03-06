<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message;

class ExcessCargoMessage extends AbstractVesselMessage
{
	protected string $level = Message::EVENT;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is overloaded and takes damage.';
	}
}
