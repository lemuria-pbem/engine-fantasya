<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\Party\AbstractPartyMessage;

class NextMessage extends AbstractPartyMessage
{
	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Finishing turn for party ' . $this->id . '.';
	}
}
