<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Filter;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\AnnouncementPartyMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Filter;

/**
 * This filter retains all but announcement messages.
 */
final class PartyAnnouncementFilter implements Filter
{
	public function retains(Message $message): bool {
		/** @var LemuriaMessage $message */
		return $message->MessageType() instanceof AnnouncementPartyMessage;
	}
}
