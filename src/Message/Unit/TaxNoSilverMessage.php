<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TaxNoSilverMessage extends AbstractNoResourcesMessage
{
	protected function create(): string {
		return 'The poor peasants in region ' . $this->region . ' do not have any silver they could pay.';
	}
}
