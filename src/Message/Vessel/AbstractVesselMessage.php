<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\AbstractMessage;
use Lemuria\Model\Domain;

abstract class AbstractVesselMessage extends AbstractMessage
{
	public function Report(): Domain {
		return Domain::Vessel;
	}
}
