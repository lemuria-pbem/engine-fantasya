<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use Lemuria\Id;
use Lemuria\Serializable;

interface Message extends \Stringable, Serializable
{
	public function Id(): Id;
}
