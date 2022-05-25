<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class AirshipCannotContinueMessage extends AirshipCannotLiftMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot spend enough aura to keep the vessel ' . $this->vessel . ' up in the air.';
	}
}
