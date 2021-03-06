<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Entertaining;

class EntertainNoExperienceMessage extends AbstractNoExperienceMessage
{
	protected function getTalent(): Talent {
		return self::createTalent(Entertaining::class);
	}
}
