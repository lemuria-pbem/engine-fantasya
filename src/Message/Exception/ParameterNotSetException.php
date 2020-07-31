<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Exception;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class ParameterNotSetException extends \UnexpectedValueException
{
	/**
	 * @param LemuriaMessage $message
	 * @param string $name
	 */
	public function __construct(LemuriaMessage $message, string $name) {
		parent::__construct('Message ' . $message->Id() . ' has no parameter ' . $name . '.');
	}
}
