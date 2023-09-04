<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party\Event;

use Lemuria\Engine\Fantasya\Message\Party\AbstractPartyMessage;
use Lemuria\Engine\Message\Result;

class DroughtMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'Since months it has not been raining. A drought lets wither many trees in the higher regions.';
	}
}
