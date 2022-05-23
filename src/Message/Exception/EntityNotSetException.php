<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Exception;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class EntityNotSetException extends \UnexpectedValueException
{
	public function __construct(LemuriaMessage $message, string $name) {
		parent::__construct('Message ' . $message->Id() . ' has no entity ' . $name . '.');
	}
}
