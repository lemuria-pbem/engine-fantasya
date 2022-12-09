<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message\Result;

class BannerMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Party ' . $this->id . ' now has a new banner.';
	}
}
