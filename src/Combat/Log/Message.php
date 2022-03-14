<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use Lemuria\Id;
use Lemuria\Serializable;
use Lemuria\Singleton;

interface Message extends \Stringable, Serializable, Singleton
{
	public function Id(): Id;
}
