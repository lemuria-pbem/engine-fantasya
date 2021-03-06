<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Talent\Taxcollecting;

class TaxOnlyMessage extends AbstractEarnOnlyMessage
{
	public function __construct() {
		$this->talent = Lemuria::Builder()->create(Taxcollecting::class);
	}
}
