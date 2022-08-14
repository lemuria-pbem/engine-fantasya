<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Filter;

use Lemuria\Engine\Fantasya\Message\Announcement;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Filter;

final class NoAnnouncementFilter implements Filter
{
	public function retains(Message $message): bool {
		/** @var LemuriaMessage $message */
		$type = $message->MessageType();
		return !($type instanceof Announcement);
	}
}
