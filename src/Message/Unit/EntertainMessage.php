<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Lemuria;
use Lemuria\Model\Lemuria\Talent\Entertaining;

class EntertainMessage extends AbstractEarnMessage
{
	public function __construct() {
		$this->talent = Lemuria::Builder()->create(Entertaining::class);
	}
}
