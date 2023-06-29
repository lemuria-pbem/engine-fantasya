<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use Lemuria\Engine\Exception\EngineException;

class OptionException extends EngineException
{
	public function __construct(string $message = 'Invalid option string.') {
		parent::__construct($message);
	}
}
