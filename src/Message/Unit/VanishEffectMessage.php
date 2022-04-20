<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class VanishEffectMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has vanished.';
	}
}
