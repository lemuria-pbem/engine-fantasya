<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TaxNoPeasantsMessage extends AbstractNoResourcesMessage
{
	protected function create(): string {
		return 'There are no peasants in region ' . $this->region . ' that could be taxed.';
	}
}
