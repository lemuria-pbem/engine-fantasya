<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception\Combat;

use Lemuria\Engine\Exception\EngineException;

class BattleEndsException extends EngineException
{
	public function __construct(string $message, \Throwable $previous = null) {
		parent::__construct($message, previous: $previous);
	}
}
