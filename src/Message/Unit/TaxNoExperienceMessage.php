<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Taxcollecting;

class TaxNoExperienceMessage extends AbstractNoExperienceMessage
{
	protected function getTalent(): Talent {
		return self::createTalent(Taxcollecting::class);
	}
}
