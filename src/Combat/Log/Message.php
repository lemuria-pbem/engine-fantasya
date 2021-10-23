<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use Lemuria\Serializable;
use Lemuria\Singleton;

/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 */
interface Message extends \Stringable, Serializable, Singleton
{
}
