<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Lemuria;
use Lemuria\Model\Lemuria\Talent\Taxcollecting;

class TaxOnlyMessage extends AbstractEarnOnlyMessage
{
	public function __construct() {
		$this->talent = Lemuria::Builder()->create(Taxcollecting::class);
	}
}
