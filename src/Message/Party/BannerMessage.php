<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;

class BannerMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Party ' . $this->id . ' now has a new banner.';
	}
}
