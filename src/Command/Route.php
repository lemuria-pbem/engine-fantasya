<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * Implementation of command ROUTE.
 *
 * - ROUTE <direction>|Pause [<direction>|Pause...]
 */
final class Route extends Travel
{
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->directions->setIsRotating();
	}
}
