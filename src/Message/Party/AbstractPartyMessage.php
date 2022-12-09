<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\AbstractMessage;
use Lemuria\Model\Domain;

abstract class AbstractPartyMessage extends AbstractMessage
{
	public function Report(): Domain {
		return Domain::Party;
	}
}
