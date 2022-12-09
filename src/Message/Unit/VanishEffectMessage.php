<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class VanishEffectMessage extends AbstractUnitMessage
{
	protected Result $result = Result::EVENT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has vanished.';
	}
}
