<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\AbstractMessage;
use Lemuria\Model\Domain;

abstract class AbstractUnitMessage extends AbstractMessage
{
	public function Report(): Domain {
		return Domain::Unit;
	}
}
