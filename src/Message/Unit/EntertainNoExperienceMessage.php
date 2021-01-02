<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Model\Lemuria\Talent;
use Lemuria\Model\Lemuria\Talent\Entertaining;

class EntertainNoExperienceMessage extends AbstractNoExperienceMessage
{
	protected function getTalent(): Talent {
		return self::createTalent(Entertaining::class);
	}
}
