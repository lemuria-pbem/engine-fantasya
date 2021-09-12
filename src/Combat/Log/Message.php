<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use Lemuria\Engine\Fantasya\Combat\Battle;
use Lemuria\Serializable;

interface Message extends \Stringable, Serializable
{
	public function __construct(Battle $battle);
}
