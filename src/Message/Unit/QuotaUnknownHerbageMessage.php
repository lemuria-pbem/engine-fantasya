<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class QuotaUnknownHerbageMessage extends QuotaRemoveHerbMessage
{
	public function create(): string {
		return 'Unit ' . $this->id . ' cannot set a quota for herbs in region ' . $this->region . ' because we have not explored it yet.';
	}
}
