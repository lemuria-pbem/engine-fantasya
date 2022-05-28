<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Filter;

use Lemuria\Engine\Fantasya\Message\Construction\AnnouncementConstructionMessage;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\AnnouncementPartyMessage;
use Lemuria\Engine\Fantasya\Message\Region\AnnouncementRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementAnonymousMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\AnnouncementVesselMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Filter;

final class NoAnnouncementFilter implements Filter
{
	private const CLASSES = [
		AnnouncementConstructionMessage::class,
		AnnouncementPartyMessage::class,
		AnnouncementRegionMessage::class,
		AnnouncementAnonymousMessage::class,
		AnnouncementVesselMessage::class
	];

	public function retains(Message $message): bool {
		/** @var LemuriaMessage $message */
		foreach (self::CLASSES as $class) {
			if ($message->isInstanceOf($class)) {
				return false;
			}
		}
		return true;
	}
}
