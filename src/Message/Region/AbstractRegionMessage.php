<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\AbstractMessage;
use Lemuria\Model\Domain;

abstract class AbstractRegionMessage extends AbstractMessage
{
	public function Report(): Domain {
		return Domain::Location;
	}
}
