<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class FiefCentralMessage extends FiefNoneMessage
{
	protected function create(): string {
		return 'A realm can only be handed over in its central region.';
	}
}
