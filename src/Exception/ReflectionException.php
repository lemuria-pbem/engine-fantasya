<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Exception\LemuriaException;

class ReflectionException extends LemuriaException
{
	public function __construct(string $message, string $class, ?\ReflectionException $previous = null) {
		parent::__construct($message . ': ' . $class, previous: $previous);
	}
}
