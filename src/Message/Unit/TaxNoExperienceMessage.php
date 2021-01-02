<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Model\Lemuria\Talent;
use Lemuria\Model\Lemuria\Talent\Taxcollecting;

class TaxNoExperienceMessage extends AbstractNoExperienceMessage
{
	protected function getTalent(): Talent {
		return self::createTalent(Taxcollecting::class);
	}
}
