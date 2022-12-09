<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\AbstractMessage;
use Lemuria\Model\Domain;

abstract class AbstractConstructionMessage extends AbstractMessage
{
	public function Report(): Domain {
		return Domain::Construction;
	}
}
