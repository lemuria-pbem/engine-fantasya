<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class QuotaNoHerbMessage extends QuotaRemoveHerbMessage
{
	public function create(): string {
		return 'There is no quota set for herbs in region ' . $this->region . '.';
	}
}
