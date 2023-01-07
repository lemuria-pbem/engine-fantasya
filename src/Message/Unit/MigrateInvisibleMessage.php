<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class MigrateInvisibleMessage extends MigrateNotFoundMessage
{
	protected Reliability $reliability = Reliability::Unreliable;
}
