<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Talent\Entertaining;

class EntertainMessage extends AbstractEarnMessage
{
	public function __construct() {
		$this->talent = Lemuria::Builder()->create(Entertaining::class);
	}
}
